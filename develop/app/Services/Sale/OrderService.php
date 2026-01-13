<?php

/**
 * 売上管理用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Sale;

use App\Consts\SessionConst;
use App\Enums\LinkPos;
use App\Enums\OrderType;
use App\Enums\RoundingMethodType;
use App\Enums\SalesClassification;
use App\Enums\TransactionType;
use App\Helpers\LogHelper;
use App\Helpers\MathHelper;
use App\Helpers\MessageHelper;
use App\Helpers\OrderNumberHelper;
use App\Helpers\TaxHelper;
use App\Http\Requests\Sale\OrderEditRequest;
use App\Models\Invoice\ChargeData;
use App\Models\Invoice\ChargeDataSalesOrder;
use App\Models\Master\MasterBranch;
use App\Models\Master\MasterConsumptionTax;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterCustomerProduct;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterRecipient;
use App\Models\Master\MasterUnit;
use App\Models\Sale\SalesOrder;
use App\Models\Sale\SalesOrderDetail;
use App\Repositories\Master\CustomerRepository;
use App\Repositories\Receive\OrdersReceivedRepository;
use App\Repositories\Sale\OrderRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 売上管理用サービス
 */
class OrderService
{
    use SessionConst;

    protected OrderRepository $repository;

    protected CustomerRepository $customer;

    protected OrdersReceivedRepository $received_repository;

    /**
     * リポジトリをインスタンス
     *
     * @param OrderRepository $repository
     * @param CustomerRepository $customer
     * @param OrdersReceivedRepository $received_repository
     */
    public function __construct(OrderRepository $repository,
        CustomerRepository $customer,
        OrdersReceivedRepository $received_repository
    ) {
        $this->repository = $repository;
        $this->customer = $customer;
        $this->received_repository = $received_repository;
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
                /** 取引種別データ */
                'transaction_types' => TransactionType::asSelectArray(),
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('sort_code')->get(),
                /** 支所データ */
                'branches' => MasterBranch::query()->get(),
                /** 納品先データ */
                'recipients' => MasterRecipient::getListSelectBox(),
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 支所データ */
                'office_facilities' => MasterOfficeFacility::query()->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $input_data,
            /** 受注伝票リポジトリ */
            'received_repository' => $this->received_repository,
            /** 検索結果 */
            'search_result' => [
                'sales_orders' => $this->repository->getSearchResult($input_data),
                'sales_orders_total' => $this->repository->getSearchResultTotal($input_data),
            ],
        ];
    }

    /**
     * 新規登録画面
     *
     * @param SalesOrder $target_data
     * @return array
     */
    public function create(SalesOrder $target_data): array
    {
        $target_date = $target_data->order_date ?? Carbon::now()->format('Y-m-d');

        return [
            /** 入力項目 */
            'input_items' => [
                /** 取引種別データ */
                'transaction_types' => TransactionType::asSelectArray(),
                /** 売上分類データ */
                'sales_classifications' => SalesClassification::asSelectArray(),
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('sort_code')->get(),
                /** 得意先_商品リレーション */
                'customers_products' => MasterCustomerProduct::query()->get(),
                /** 商品データ */
                'products' => MasterProduct::getProductData(),
                /** 単位マスター */
                'units' => MasterUnit::query()->oldest('code')->get(),
                /** 税率リスト */
                'consumption_taxes' => MasterConsumptionTax::getList(),
                /** 支所データ */
                'branches' => MasterBranch::query()->get(),
                /** 納品先データ */
                'recipients' => MasterRecipient::getListSelectBox(),
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 事業所データ */
                'office_facilities' => MasterOfficeFacility::query()->get(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** 共通使用セッションキー(URL) */
            'session_common_key' => $this->refURLCommonKey(),
            /** 税率の初期値 */
            'default_tax_list' => TaxHelper::getTaxRate($target_date),
        ];
    }

    /**
     * 新規登録・複製処理
     *
     * @param OrderEditRequest $request
     * @return array
     *
     * @throws Exception
     */
    public function store(OrderEditRequest $request): array
    {
        $error_flag = false;

        $order_number = OrderNumberHelper::getOrderNumber(1, OrderType::SALES); // 伝票番号

        DB::beginTransaction();

        try {
            $billing_customer_id = $this->customer
                ->getBillingCustomerId($request->customer_id);
            // リクエストキーのデフォルトセット
            $default_values = [
                'order_number' => $order_number,
                'recipient_id' => null,
                'billing_customer_id' => $billing_customer_id,
                'tax_calc_type_id' => $this->customer->getTaxCalcTypeId($billing_customer_id),
                'sales_classification_id' => SalesClassification::CLASSIFICATION_SALE,
                'link_pos' => LinkPos::SALES_MANAGEMENT,
                'creator_id' => Auth::user()->id,
                'updated_id' => Auth::user()->id,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 売上伝票を登録
            $order = $this->repository->createSalesOrder($request->input());

            $sort_index = 1;
            $details = [];
            // 売上伝票詳細の登録内容セット
            foreach ($request->detail ?? [] as $detail) {
                $product = MasterProduct::query()->find($detail['product_id']);

                $consumption_tax_rate = $detail['consumption_tax_rate'] ?? 0;
                $reduced_tax_flag = $detail['reduced_tax_flag'] ?? 0;

                $details[] = [
                    'sales_order_id' => $order->id,
                    'product_id' => $detail['product_id'],
                    'product_name' => $detail['product_name'],
                    'quantity' => $detail['quantity'] ?? 0,
                    'unit_name' => $detail['unit_name'] ?? '',
                    'unit_price' => $detail['unit_price'] ?? 0,
                    'discount' => $detail['discount'] ?? 0,
                    'purchase_unit_price' => $detail['purchase_unit_price'] ?? 0,
                    'gross_profit' => $detail['gross_profit'] ?? 0,

                    'unit_price_decimal_digit' => $product->unit_price_decimal_digit,
                    'quantity_decimal_digit' => $product->quantity_decimal_digit,
                    'quantity_rounding_method_id' => $product->quantity_rounding_method_id,
                    'amount_rounding_method_id' => $product->amount_rounding_method_id,
                    'sub_total' => MathHelper::getRoundingValue($detail['quantity'] * $detail['unit_price'],
                        0, config('consts.default.common.sub_total_rounding_method')),
                    'sub_total_tax' => $detail['tax'],

                    'tax_type_id' => $detail['tax_type_id'] ?? 0,
                    'consumption_tax_rate' => $consumption_tax_rate,
                    'reduced_tax_flag' => $reduced_tax_flag,
                    'rounding_method_id' => $this->customer
                        ->getTaxRoundingMethodId($billing_customer_id) ?? RoundingMethodType::ROUND_OFF,
                    'note' => $detail['note'],
                    'sort' => $sort_index,
                ];

                ++$sort_index;
            }
            // 売上伝票詳細を登録
            $order = $this->repository->createSalesOrderDetails($order, $details);

            $order_details = $order->salesOrderDetail()->get();
            foreach ($order_details ?? [] as $detail) {
                // 最終単価更新
                // $this->repository->upsertUnitPrice($order->customer_id, $detail);
                $this->repository->upsertCustomerPrice($order->customer_id, $order->order_date, $detail);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            // フラッシュメッセージ用にインスタンス作成
            $order = SalesOrder::query()
                ->firstOrNew(['order_number' => $order_number]);

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getOrderStoreMessage($error_flag, $order->order_number_zero_fill);

        return [$error_flag, $message];
    }

    /**
     * 編集画面
     *
     * @param SalesOrder $target_data
     * @return array
     */
    public function edit(SalesOrder $target_data): array
    {
        return $this->create($target_data);
    }

    /**
     * 更新処理
     *
     * @param OrderEditRequest $request
     * @param SalesOrder $order
     * @return array
     *
     * @throws Exception
     */
    public function update(OrderEditRequest $request, SalesOrder $order): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $customer = MasterCustomer::query()->find($request->customer_id);
            // リクエストキーのデフォルトセット
            $default_values = [
                'recipient_id' => null,
                'billing_customer_id' => $customer->billing_customer_id,
                'tax_calc_type_id' => $customer->tax_calc_type_id,
                'sales_classification_id' => SalesClassification::CLASSIFICATION_SALE,
                'updated_id' => Auth::user()->id,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 売上伝票を更新
            $order = $this->repository->updateSalesOrder($order, $request->input());

            // 売上伝票詳細を更新
            $sort_index = 1;
            $sort_numbers = [];
            foreach ($request->detail ?? [] as $detail) {
                $detail['quantity'] = $detail['quantity'] ?? 0;
                $detail['unit_name'] = $detail['unit_name'] ?? '';
                $detail['unit_price'] = $detail['unit_price'] ?? 0;
                $detail['discount'] = $detail['discount'] ?? 0;
                $detail['purchase_unit_price'] = $detail['purchase_unit_price'] ?? 0;
                $detail['gross_profit'] = $detail['gross_profit'] ?? 0;

                $product = MasterProduct::query()->find($detail['product_id']);
                $detail['unit_price_decimal_digit'] = $product->unit_price_decimal_digit;
                $detail['quantity_decimal_digit'] = $product->quantity_decimal_digit;
                $detail['quantity_rounding_method_id'] = $product->quantity_rounding_method_id;
                $detail['amount_rounding_method_id'] = $product->amount_rounding_method_id;
                $detail['sub_total'] = MathHelper::getRoundingValue($detail['quantity'] * $detail['unit_price'],
                    0, config('consts.default.common.sub_total_rounding_method'));
                $detail['sub_total_tax'] = $detail['tax'];

                $detail['consumption_tax_rate'] = $detail['consumption_tax_rate'] ?? 0;
                $detail['reduced_tax_flag'] = $detail['reduced_tax_flag'] ?? 0;
                $detail['rounding_method_id'] = $detail['tax_rounding_method_id'] ?? RoundingMethodType::ROUND_OFF;
                $detail['sort'] = $sort_index;
                $detail['deleted_at'] = null;

                // 売上伝票詳細を更新
                $order = $this->repository->updateSalesOrderDetails($order, (new SalesOrderDetail($detail))->toArray());

                // 売上伝票詳細のレコード削除の為、ソートNo取得
                $sort_numbers[] = $sort_index;

                ++$sort_index;
            }
            // 更新後に無いレコード削除
            $order = $this->repository->deleteOrderDetailsForUpdate($order, $sort_numbers);

            $order_details = $order->salesOrderDetail()->get();
            foreach ($order_details ?? [] as $detail) {
                // 最終単価更新
                // $this->repository->upsertUnitPrice($order->customer_id, $detail);
                $this->repository->upsertCustomerPrice($order->customer_id, $order->order_date, $detail);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getOrderUpdateMessage($error_flag, $order->order_number_zero_fill);

        return [$error_flag, $message];
    }

    /**
     * 削除処理
     *
     * @param SalesOrder $order
     * @return array
     *
     * @throws Exception
     */
    public function destroy(SalesOrder $order): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $this->repository->deleteSalesOrder($order); // 論理削除

            /** 請求データ_売上伝票リレーション削除 */
            $charge_data_sales_order = ChargeDataSalesOrder::query()
                ->where('sales_order_id', $order->id)
                ->first();
            if ($charge_data_sales_order != null) {
                $charge_data_sales_order->delete();

                /** 請求データ修正 */
                $charge_data = ChargeData::query()
                    ->where('id', $charge_data_sales_order->charge_data_id)
                    ->first();
                $charge_data->sales_total -= $order->sales_total; // 売上合計
                $charge_data->charge_total = $charge_data->calculated_charge_total; // 今回請求額
                $charge_data->save();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getOrderDestroyMessage($error_flag, $order->order_number_zero_fill);

        return [$error_flag, $message];
    }
}
