<?php

/**
 * 受注管理用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Receive;

use App\Consts\SessionConst;
use App\Enums\InventoryType;
use App\Enums\OrderStatus;
use App\Enums\ReducedTaxFlagType;
use App\Enums\RoundingMethodType;
use App\Enums\SalesConfirm;
use App\Enums\TaxType;
use App\Enums\TransactionType;
use App\Helpers\DateHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\LogHelper;
use App\Helpers\MathHelper;
use App\Helpers\MessageHelper;
use App\Helpers\ProductHelper;
use App\Helpers\TaxHelper;
use App\Http\Requests\Receive\OrdersReceivedEditRequest;
use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterBranch;
use App\Models\Master\MasterConsumptionTax;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterHonorificTitle;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterProductUnit;
use App\Models\Master\MasterRecipient;
use App\Models\Master\MasterUnit;
use App\Models\Master\MasterWarehouse;
use App\Models\Receive\OrdersReceived;
use App\Models\Receive\OrdersReceivedDetail;
use App\Models\Receive\OrdersReceivedStatusHistory;
use App\Models\Sale\SalesOrder;
use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Inventory\InventoryStockDataRepository;
use App\Repositories\Master\RecipientRepository;
use App\Repositories\Receive\OrdersReceivedRepository;
use App\Repositories\Sale\OrderRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 受注管理用サービス
 */
class OrdersReceivedService
{
    use SessionConst;

    protected OrdersReceivedRepository $repository;

    protected RecipientRepository $recipient;

    protected OrderRepository $sales_order;

    protected InventoryStockDataRepository $inventory_stock_data;

    protected InventoryRepository $inventory_data;

    /**
     * リポジトリをインスタンス
     *
     * @param OrdersReceivedRepository $repository
     * @param RecipientRepository $recipient
     * @param OrderRepository $sales_order
     * @param InventoryStockDataRepository $inventory_stock_data
     * @param InventoryRepository $inventory_data
     */
    public function __construct(OrdersReceivedRepository $repository,
        RecipientRepository $recipient,
        OrderRepository $sales_order,
        InventoryStockDataRepository $inventory_stock_data,
        InventoryRepository $inventory_data
    ) {
        $this->repository = $repository;
        $this->recipient = $recipient;
        $this->sales_order = $sales_order;
        $this->inventory_stock_data = $inventory_stock_data;
        $this->inventory_data = $inventory_data;
    }

    /**
     * 一覧画面
     *
     * @param array $input_data
     * @return array
     */
    public function index(array $input_data): array
    {
        return [
            /** 検索項目 */
            'search_items' => [
                /** 担当マスター */
                'employees' => MasterEmployee::query()->get(),
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('sort_code')->get(),
                /** 支所データ */
                'branches' => MasterBranch::query()->get(),
                /** 納品先データ */
                'recipients' => MasterRecipient::getListSelectBox(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $input_data,
            /** 検索結果 */
            'search_result' => [
                'orders_received' => $this->repository->getSearchResult($input_data),
            ],
        ];
    }

    /**
     * 新規登録画面
     *
     * @param OrdersReceived $target_data
     * @return array
     */
    public function create(OrdersReceived $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 担当者マスター */
                'employees' => MasterEmployee::query()->oldest('code')->get(),
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('sort_code')->get(),
                /** 商品データ */
                'products' => MasterProduct::getProductData(),
                /** 単位マスター */
                'units' => MasterUnit::query()->oldest('code')->get(),
                /** 税率 */
                'tax_rates' => MasterConsumptionTax::getList(),
                /** 支所データ */
                'branches' => MasterBranch::query()->get(),
                /** 倉庫データ */
                'warehouses' => MasterWarehouse::query()->get(),
                /** 納品先データ */
                'recipients' => MasterRecipient::getListSelectBox(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** 共通使用セッションキー(URL) */
            'session_common_key' => $this->refURLCommonKey(),
        ];
    }

    /**
     * 新規登録処理(「売上確定」押下時は売上伝票を作成）
     *
     * @param OrdersReceivedEditRequest $request
     * @return array
     *
     * @throws Exception
     */
    public function store(OrdersReceivedEditRequest $request): array
    {
        $error_flag = false;
        $isCopy = ($request->copy_number !== '0');
        $OrdersReceived_number = OrdersReceived::withTrashed()->max('id') + 1;
        // デバッグ用データ格納用
        $inventory_data = $updating_inventory_stock_data = $updated_inventory_stock_data =
        $inventory_data_closing = $sales_order = [];

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'order_number' => $OrdersReceived_number,
                'order_status' => SalesConfirm::UNSETTLED,
                'recipient_id' => $this->recipient->setRecipientId($request),
                'employee_id' => Auth::user()->employee_id,
                'sales_total' => 0,
                'updated_id' => Auth::user()->id,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 受注伝票を登録
            $OrdersReceived = $this->repository->createOrdersReceived($request->input());

            $sort_index = 1;
            $details = [];
            // 受注伝票詳細の登録内容セット
            foreach ($request->detail as $detail) {
                // リクエストキー(detail内)のデフォルトセット
                $warehouse_id = $detail['warehouse_id'] ?? null;
                $quantity = $detail['quantity'] ?? 0;
                $delivery_date = $detail['delivery_date'] ?? null;
                $sales_confirm = $detail['sales_confirm'] ?? null;
                // 複製時
                if ($isCopy) {
                    $warehouse_id = null;
                    $quantity = 0;
                    $delivery_date = null;
                    $sales_confirm = null;
                }

                $details[] = [
                    'orders_received_id' => $OrdersReceived->id,
                    'warehouse_id' => $warehouse_id,
                    'product_id' => $detail['product_id'],
                    'product_name' => $detail['product_name'],
                    'quantity' => $quantity,
                    'unit_name' => $detail['unit_name'] ?? '@',
                    'unit_price' => $detail['unit_price'] ?? 0,
                    'consumption_tax_rate' => $detail['consumption_tax_rate'] ?? 0,
                    'reduced_tax_flag' => $detail['reduced_tax_flag'] ?? 0,
                    'rounding_method_id' => $detail['rounding_method_id'] ?? RoundingMethodType::ROUND_OFF,
                    'delivery_date' => $delivery_date,
                    'note' => $detail['note'],
                    'sort' => $sort_index,
                    'sales_confirm' => $sales_confirm,
                ];

                ++$sort_index;
            }
            // 受注伝票詳細を登録
            $OrdersReceived = $this->repository
                ->createOrdersReceivedDetails($details);

            if (!$this->repository->getDeliveryDateFlg($OrdersReceived)) {
                // 全ての受注詳細に納品日が入っている時、order_statusを1に変更
                $this->repository->setSalesConfirm($OrdersReceived);
            }

            $sort_index = 1;
            $sort_numbers = [];
            foreach ($request->detail ?? [] as $detail) {
                $request_sales_confirm = $detail['sales_confirm'] ?? null;
                // 売上確定フラグが立っている場合、ソートNo取得
                if ($request_sales_confirm) {
                    $sort_numbers[] = $sort_index;
                }

                ++$sort_index;
            }

            if (!$this->repository->getDetailBySalesConfirm()->isEmpty() && $sort_numbers) {
                // 売上確定フラグが立っている明細がある時、売上テーブルに登録
                $sales_order[] = $this->insertSalesOrder($OrdersReceived, $sort_numbers);
            }

            $after_order_data = $OrdersReceived->ordersReceivedDetail()->get()->toArray();

            // 現在庫データ作成
            [$updating_inventory_stock_data, $updated_inventory_stock_data] =
                InventoryHelper::setDataForUpdatingAndUpdated($after_order_data);

            // 現在庫、在庫データへ登録
            [$inventory_data, $inventory_data_closing] =
                InventoryHelper::inventoryStore(
                    $after_order_data,
                    $isCopy
                );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            // フラッシュメッセージ用にインスタンス作成
            $OrdersReceived = OrdersReceived::query()
                ->firstOrNew(['order_number' => $OrdersReceived_number]);
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        } finally {
            $debug_data = [
                /** デバッグ用の受注伝票 */
                'created_orders_received' => $OrdersReceived->ordersReceivedDetail()->get()->toArray(),
                /** デバッグ用の在庫データ */
                'inventory_data' => $inventory_data,
                /** デバッグ用の現在庫データ-更新前 */
                'updating_inventory_stock_data' => $updating_inventory_stock_data,
                /** デバッグ用の現在庫データ-更新後 */
                'updated_inventory_stock_data' => $updated_inventory_stock_data,
                /** デバッグ用の締在庫数 */
                'inventory_data_closing' => $inventory_data_closing,
                /** デバッグ用の売上伝票 */
                'sales_order' => $sales_order,
            ];

            // デバッグ用ログ出力
            foreach ($debug_data as $table => $data) {
                if (!empty($data)) {
                    Log::channel('debug')
                        ->debug($table . ' orders_received_id:' . $OrdersReceived->id,
                            [$table => $data]
                        );
                }
            }
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getStoreMessage($error_flag, $OrdersReceived->order_number_zero_fill, 'received');

        return [$error_flag, $message];
    }

    /**
     * 編集画面
     *
     * @param OrdersReceived $target_data
     * @return array
     */
    public function edit(OrdersReceived $target_data): array
    {
        $add_data = [
            /** 入力項目 */
            'input_items' => [
                'order_status' => OrderStatus::asSelectArray(),
            ],
            /** 受注伝票状態履歴 */
            'status_history' => OrdersReceivedStatusHistory::query()
                ->where('orders_received_id', $target_data->id)
                ->orderByDesc('created_at')
                ->get(),
            /** 登録納品日の有無 */
            'delivery_date_exists' => $target_data->ordersReceivedDetail()
                ->whereNotNull('delivery_date')
                ->exists(),
        ];

        return array_merge_recursive($this->create($target_data), $add_data);
    }

    /**
     * 更新処理
     *
     * @param OrdersReceivedEditRequest $request
     * @param OrdersReceived $target_data
     * @return array
     *
     * @throws Exception
     */
    public function update(OrdersReceivedEditRequest $request, OrdersReceived $target_data): array
    {
        $error_flag = false;
        // 更新前のデータを取得(配列)
        $before_order_data = $target_data->ordersReceivedDetail()->get()->toArray();
        // 更新前のデータを取得(Collection)
        $before_target_data = $target_data->ordersReceivedDetail()->get();
        // デバッグ用データ格納用
        $updating_inventory_stock_data = $updated_inventory_stock_data = $inventory_data =
        $return_inventory_stock_data = $inventory_data_closing = $sales_order = [];

        DB::beginTransaction();

        try {
            // 一度すべて在庫返品する
            $details = (new OrdersReceivedDetail())->query()
                ->where('orders_received_id', $target_data->id)
                ->get();

            foreach ($details as $detailData) {
                if (!isset($detailData->warehouse_id) || !isset($detailData->product_id)) {
                    continue;
                }
                $exists = $this->inventory_stock_data
                    ->existsInventoryStockData($detailData->warehouse_id, $detailData->product_id);

                if (!$exists) {
                    continue;
                }

                $arrVal = [
                    'orders_received_id' => $target_data->id,
                    'product_id' => $detailData->product_id,
                    'warehouse_id' => $detailData->warehouse_id,
                    'quantity' => $detailData->quantity,
                ];

                // 更新前のデータ取得
                $updating_inventory_stock_data[] = $this->inventory_stock_data
                    ->getInventoryStockData($detailData->warehouse_id, $detailData->product_id);

                // 返品
                $return_inventory_stock_data[] = InventoryHelper::returnInventory($arrVal);
            }

            // リクエストキーのデフォルトセット
            $default_values = [
                'order_status' => SalesConfirm::UNSETTLED,
                'recipient_id' => $this->recipient->setRecipientId($request),
                'sales_total' => 0,
                'updated_id' => Auth::user()->id,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 受注伝票を更新
            $target_data_detail = $this->repository->updateOrdersReceived($target_data, $request->input());
            $sort_index = 1;
            $sort_numbers = [];
            foreach ($request->detail ?? [] as $detail) {
                // リクエストキー(detail内)のデフォルトセット
                $detail['orders_received_id'] = $target_data->id;
                $detail['warehouse_id'] = $detail['warehouse_id'] ?? null;
                $detail['quantity'] = $detail['quantity'] ?? 0;
                $detail['unit_name'] = $detail['unit_name'] ?? '@';
                $detail['unit_price'] = $detail['unit_price'] ?? 0;
                $detail['consumption_tax_rate'] = $detail['consumption_tax_rate'] ?? 0;
                $detail['reduced_tax_flag'] = $detail['reduced_tax_flag'] ?? 0;
                $detail['rounding_method_id'] = $detail['rounding_method_id'] ?? RoundingMethodType::ROUND_OFF;
                $detail['delivery_date'] = $detail['delivery_date'] ?? null;
                $detail['sales_confirm'] = $detail['sales_confirm'] ?? null;
                $detail['sort'] = $sort_index;
                $detail['deleted_at'] = null;

                // 受注伝票詳細を更新
                $target_data_detail = $this->repository
                    ->updateOrdersReceivedDetails($target_data, (new OrdersReceivedDetail($detail))->toArray());

                // 受注伝票詳細のレコード削除の為、ソートNo取得
                $sort_numbers[] = $sort_index;

                ++$sort_index;
            }
            // 更新後に無いレコード削除
            $target_data_detail = $this->repository
                ->deleteOrderDetailsForUpdate($target_data, $sort_numbers);

            $sort_index = 1;
            $sort_numbers = [];
            foreach ($request->detail ?? [] as $detail) {
                // 更新前の受注伝票詳細から対象のsales_confirmの値を取得
                $sales_confirm = $before_target_data
                    ->where('sort', $sort_index)
                    ->pluck('sales_confirm')
                    ->get(0) ?? null;

                $request_sales_confirm = isset($detail['sales_confirm']) ? (int) $detail['sales_confirm'] : null;
                // 売上確定フラグが更新された、且つ、売上確定フラグが立っている場合、変更された値を取得し、ソートNo取得
                if ($request_sales_confirm !== $sales_confirm) {
                    $sort_numbers[] = $sort_index;
                }

                ++$sort_index;
            }

            // 全ての受注詳細に納品日が入っている時、order_statusを1に変更
            if (!$this->repository->getDeliveryDateFlg($target_data)) {
                $this->repository->setSalesConfirm($target_data);
            }

            // 売上確定フラグが立っている、且つ、売上確定フラグが更新された明細がある時、売上テーブルに登録
            if (!$this->repository->getDetailBySalesConfirm()->isEmpty() && $sort_numbers) {
                $sales_order[] = $this->insertSalesOrder($target_data, $sort_numbers);
            }

            // 更新後のデータを取得
            $after_order_data = $target_data->ordersReceivedDetail()->get()->toArray();

            // 現在庫データ作成
            [$updating_inventory_stock_data, $updated_inventory_stock_data] =
                InventoryHelper::setDataForUpdatingAndUpdated($after_order_data);

            // 在庫データへ登録
            [$inventory_data, $inventory_data_closing] =
                InventoryHelper::inventoryUpdate($after_order_data, $before_order_data);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        } finally {
            // デバッグ用の現在庫データ(返品処理)ログ出力
            if (!empty($return_inventory_stock_data)) {
                Log::channel('debug')
                    ->debug('returned_inventory_stock_data orders_received_id:' . $target_data->id,
                        ['returned_inventory_stock_data' => $return_inventory_stock_data]
                    );
            }

            $debug_data = [
                /** デバッグ用の受注伝票-更新前 */
                'updating_orders_received' => $before_order_data,
                /** デバッグ用の受注伝票-更新後 */
                'updated_orders_received' => $target_data->ordersReceivedDetail()->get()->toArray(),
                /** デバッグ用の在庫データ */
                'inventory_data' => $inventory_data,
                /** デバッグ用の現在庫データ-更新前 */
                'updating_inventory_stock_data' => $updating_inventory_stock_data,
                /** デバッグ用の現在庫データ-更新後 */
                'updated_inventory_stock_data' => $updated_inventory_stock_data,
                /** デバッグ用の締在庫数 */
                'inventory_data_closing' => $inventory_data_closing,
            ];
            // デバッグ用ログ出力
            foreach ($debug_data as $table => $data) {
                if (!empty($data)) {
                    Log::channel('debug')
                        ->debug($table . ' orders_received_id:' . $target_data->id,
                            [$table => $data]
                        );
                }
            }

            // デバッグ用の売上伝票ログ出力
            if (!empty($sales_order)) {
                Log::channel('debug')
                    ->debug('created_sales_order orders_received_id:' . $target_data->id,
                        ['sales_order' => $sales_order]
                    );
            }
        }

        $message = MessageHelper::getUpdateMessage($error_flag, $target_data->order_number_zero_fill, 'received');

        return [$error_flag, $message];
    }

    /**
     * 削除処理
     *
     * @param OrdersReceived $target_data
     * @return array
     *
     * @throws Exception
     */
    public function destroy(OrdersReceived $target_data): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $orders_received_details = $target_data->ordersReceivedDetail->toArray();

            $details = [];
            foreach ($orders_received_details as $item) {
                if (!$item['delivery_date']) {
                    continue;
                }

                // 数量 + 現在庫数
                $item['inventory_stocks'] = $item['quantity'] +
                    $this->inventory_stock_data->getInventoryStock($item['warehouse_id'], $item['product_id']);
                $item['updated_id'] = Auth::user()->id;
                // 現在庫データを更新
                $this->inventory_stock_data
                    ->updateInventoryStockData((new InventoryStockData($item))->toArray());

                // 在庫データ登録の為のデータ整形
                $item['orders_received_number'] = $item['orders_received_id'];
                $item['orders_received_details_sort'] = $item['sort'];
                $item['inout_date'] = $item['delivery_date'];
                $item['inout_status'] = 1;
                $item['from_warehouse_id'] = InventoryType::INVENTORY_OUT;
                $item['to_warehouse_id'] = $item['warehouse_id'] ?? null;
                $item['employee_id'] = Auth::user()->employee_id;
                $item['note'] = $item['note'] ?? null;
                $item['updated_id'] = Auth::user()->id;

                // 在庫データを登録
                $inventory_data = $this->inventory_data->createInventoryData($item);

                // 在庫データ詳細登録の為のデータ整形
                $details[] = [
                    'inventory_data_id' => $inventory_data->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'] ?? 0,
                    'note' => $item['note'] ?? null,
                    'sort' => 1,
                ];
            }

            // 在庫データ詳細を登録
            $this->inventory_data->createInventoryDataDetails($details);

            // 受注伝票削除
            $this->repository->deleteOrdersReceived($target_data);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        $message = MessageHelper::getDestroyMessage($error_flag, $target_data->order_number_zero_fill, 'received');

        return [$error_flag, $message];
    }

    /**
     * 売上確定時の連動売上伝票作成
     *
     * @param Model $orders_received
     * @param array $sort_numbers
     * @return Model
     */
    private function insertSalesOrder(Model $orders_received, array $sort_numbers = []): Model
    {
        $order_number = SalesOrder::withTrashed()->max('id') + 1; // 新規伝票番号

        // 売上伝票の登録内容セット
        $order_array = $orders_received->toArray();
        $order_array['order_number'] = $order_number;
        $order_array['orders_received_number'] = $orders_received->id;
        $order_array['billing_date'] = $orders_received->order_date;
        $order_array['billing_customer_id'] = $orders_received->customer_id_by_billing_customer;
        $order_array['tax_calc_type_id'] = $orders_received->tax_calc_type_id_by_billing_customer;
        $order_array['transaction_type_id'] = TransactionType::ON_ACCOUNT;
        $order_array['memo'] = null;
        $order_array['creator_id'] = Auth::user()->id;
        $order_array['updated_id'] = Auth::user()->id;

        // 売上伝票を登録
        $order = $this->sales_order->createSalesOrder($order_array);

        // 明細行の計算結果を加算して、親レコードに登録する
        $sum_sub_total = 0;

        // 売上確定フラグが立っている受注詳細のみ取得
        $received_details = $this->repository
            ->getDetailBySalesConfirm($sort_numbers);
        $sort_index = 1;
        $details = [];
        foreach ($received_details as $detail) {
            $product_unit = MasterProductUnit::query()->find($detail['product_id']);
            $unit_id = isset($product_unit->unit_id) ? (int) $product_unit->unit_id : 0;
            $unit_name = '';
            if ($unit_id !== 0) {
                $unit = MasterUnit::query()->find($unit_id);
                $unit_name = $unit->name;
            }

            $product = MasterProduct::query()->find($detail['product_id']);

            // ※CodeSpacesでは使用しない
            // $unit_price = ProductHelper::getCustomerUnitPriceArray($order->customer_id, $detail['product_id'], $unit_name);
            $unit_price = ProductHelper::getCustomerPriceArray($order->customer_id, $detail['product_id']);

            $sub_total = MathHelper::getRoundingValue($detail['quantity'] * $unit_price[0],
                0, config('consts.default.common.sub_total_rounding_method'));
            $sum_sub_total += $sub_total;

            $tax_list = TaxHelper::getTaxRate($order->order_date);
            $set_tax = ($product->reduced_tax_flag == ReducedTaxFlagType::REDUCED_TAX)
                ? $tax_list['reduced_tax_rate']
                : $tax_list['normal_tax_rate'];

            $consumption_tax_rate = $set_tax;
            $reduced_tax_flag = $product->reduced_tax_flag;
            $note = DateHelper::changeDateFormat($detail['delivery_date'], 'm/d');
            // 備考が入力されていた場合、納品日+備考をセット
            if ($detail['note']) {
                $note .= $detail['note'];
            }
            // 納品先が入力されていた場合、納品日+納品先+敬称(様)+備考をセット
            if ($orders_received->recipient_name) {
                $note .= $orders_received->recipient_name . (new MasterHonorificTitle())->name_fixed . $detail['note'];
            }

            // 売上伝票詳細の登録内容セット
            $details[] = [
                'sales_order_id' => $order->id,
                'product_id' => $detail['product_id'],
                'product_name' => $detail['product_name'],
                'quantity' => $detail['quantity'] ?? 0,
                'unit_name' => $unit_name,
                'unit_price' => $unit_price[0],
                'purchase_unit_price' => $product->purchase_unit_price,
                'gross_profit' => $detail['gross_profit'] ?? 0,

                'unit_price_decimal_digit' => $product->unit_price_decimal_digit,
                'quantity_decimal_digit' => $product->quantity_decimal_digit,
                'quantity_rounding_method_id' => $product->quantity_rounding_method_id,
                'amount_rounding_method_id' => $product->amount_rounding_method_id,
                'sub_total' => $sub_total,
                'sub_total_tax' => 0,

                'tax_type_id' => $detail['tax_type_id'] ?? TaxType::OUT_TAX,
                'consumption_tax_rate' => $consumption_tax_rate,
                'reduced_tax_flag' => $reduced_tax_flag,
                'rounding_method_id' => $orders_received
                    ->tax_rounding_method_id_by_billing_customer ?? RoundingMethodType::ROUND_OFF,
                'note' => $note,
                'sort' => $sort_index,
            ];

            // 親伝票追加更新
            $order->sales_total = $sum_sub_total;
            $order->printing_date = $detail['printing_date'];
            $order->save();

            ++$sort_index;
        }

        // 売上伝票詳細を登録
        return $this->sales_order->createSalesOrderDetails($order, $details);
    }
}
