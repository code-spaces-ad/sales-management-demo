<?php

/**
 * 仕入処理用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Trading;

use App\Consts\SessionConst;
use App\Enums\LinkPos;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PurchaseClassification;
use App\Enums\RoundingMethodType;
use App\Helpers\MathHelper;
use App\Helpers\MessageHelper;
use App\Helpers\OrderNumberHelper;
use App\Helpers\TaxHelper;
use App\Http\Requests\Trading\PurchaseOrderEditRequest;
use App\Models\Master\MasterConsumptionTax;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterSupplier;
use App\Models\Master\MasterUnit;
use App\Models\Trading\PurchaseOrder;
use App\Models\Trading\PurchaseOrderDetail;
use App\Models\Trading\PurchaseOrderStatusHistory;
use App\Repositories\Trading\PurchaseOrderRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TransactionType;

/**
 * 仕入処理用サービス
 */
class PurchaseOrderService
{
    use SessionConst;

    protected PurchaseOrderRepository $repository;

    /**
     * リポジトリをインスタンス
     *
     * @param PurchaseOrderRepository $repository
     */
    public function __construct(PurchaseOrderRepository $repository)
    {
        $this->repository = $repository;
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
                /** 仕入先マスター */
                'suppliers' => MasterSupplier::query()->oldest('name_kana')->get(),
                /** 商品マスター */
                'products' => MasterProduct::query()->orderByDesc('code')->get(),
                /** 状態 */
                'order_status' => OrderStatus::asSelectArray(),
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 支所データ */
                'office_facilities' => MasterOfficeFacility::query()->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $input_data,
            /** 検索結果 */
            'search_result' => [
                'purchase_orders' => $this->repository->getSearchResult($input_data),
                'purchase_orders_total' => $this->repository->getSearchResultTotal($input_data)[0],
            ],
        ];
    }

    /**
     * 新規登録画面
     *
     * @param PurchaseOrder $target_data
     * @return array
     */
    public function create(PurchaseOrder $target_data): array
    {
        $target_date = $target_data->order_date ?? Carbon::now()->format('Y-m-d');

        return [
            /** 入力項目 */
            'input_items' => [
                /** 取引種別データ */
                'transaction_types' => TransactionType::asSelectArray(),
                /** 担当者マスター */
                'employees' => MasterEmployee::query()->oldest('code')->get(),
                /** 仕入分類データ */
                'purchase_classifications' => PurchaseClassification::asSelectArray(),
                /** 仕入先マスター */
                'suppliers' => MasterSupplier::query()->oldest('name_kana')->get(),
                /** 単位マスター */
                'units' => MasterUnit::query()->oldest('code')->get(),
                /** 商品マスター */
                'products' => MasterProduct::getProductData(),
                /** 状態 */
                'order_status' => OrderStatus::asSelectArray(),
                /** 税率 */
                'tax_rates' => MasterConsumptionTax::getList(),
                /** 税率リスト */
                'consumption_taxes' => MasterConsumptionTax::getList(),
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
     * @param PurchaseOrderEditRequest $request
     * @return array
     *
     * @throws Exception
     */
    public function store(PurchaseOrderEditRequest $request): array
    {
        $error_flag = false;

        $order_number = OrderNumberHelper::getOrderNumber(1, OrderType::PURCHASE); // 伝票番号

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'order_number' => $order_number,
                'order_status' => 1,
                'link_pos' => LinkPos::SALES_MANAGEMENT,
                'updated_id' => Auth::user()->id,
                'purchase_classification_id' => PurchaseClassification::CLASSIFICATION_PURCHASE,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 仕入伝票を登録
            $order = $this->repository->createPurchaseOrder($request->input());

            // 仕入伝票詳細を登録
            $sort_index = 1;
            $details = [];
            foreach ($request->detail ?? [] as $detail) {
                $quantity = $detail['quantity'] ?? 0;
                $unit_price = $detail['unit_price'] ?? 0;

                $details[] = [
                    'purchase_order_id' => $order->id,
                    'product_id' => $detail['product_id'],
                    'product_name' => $detail['product_name'],
                    'quantity' => $quantity,
                    'unit_name' => $detail['unit_name'] ?? '',
                    'unit_price' => $unit_price,
                    'discount' => $detail['discount'] ?? 0,
                    'sub_total' => MathHelper::getRoundingValue($quantity * $unit_price,
                        0, config('consts.default.common.sub_total_rounding_method')),
                    'sub_total_tax' => $detail['tax'] ?? 0,
                    'tax_type_id' => $detail['tax_type_id'] ?? 0,
                    'consumption_tax_rate' => $detail['consumption_tax_rate'] ?? 0,
                    'reduced_tax_flag' => $detail['reduced_tax_flag'] ?? 0,
                    'rounding_method_id' => $detail['rounding_method_id'] ?? RoundingMethodType::ROUND_OFF,
                    'note' => $detail['note'],
                    'sort' => $sort_index,
                ];

                ++$sort_index;
            }

            // 仕入伝票詳細を登録
            $order = $this->repository->createPurchaseOrderDetails($order, $details);

            $order_details = $order->purchaseOrderDetail()->get();
            foreach ($order_details ?? [] as $detail) {
                // 最終単価更新
                $this->repository->upsertUnitPrice($order->supplier_id, $detail);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            // フラッシュメッセージ用にインスタンス作成
            $order = $this->repository->firstOrNewForOrderNumber($order_number);

            Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        // フラッシュメッセージ取得
        return [$error_flag, MessageHelper::getStoreMessage($error_flag, $order->order_number_zero_fill, 'purchase_order')];
    }

    /**
     * 編集画面
     *
     * @param PurchaseOrder $target_data
     * @return array
     */
    public function edit(PurchaseOrder $target_data): array
    {
        return array_merge_recursive($this->create($target_data),
            [
                /** 仕入伝票状態履歴 */
                'status_history' => PurchaseOrderStatusHistory::where('purchase_order_id', $target_data->id)
                    ->orderByDesc('created_at')->get(),
            ]);
    }

    /**
     * 更新処理
     *
     * @param PurchaseOrderEditRequest $request
     * @param PurchaseOrder $target_data
     * @return array
     *
     * @throws Exception
     */
    public function update(PurchaseOrderEditRequest $request, PurchaseOrder $target_data): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'order_status' => 1,
                'updated_id' => Auth::user()->id,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 仕入伝票を更新
            $target_data = $this->repository->updatePurchaseOrder($target_data, $request->input());

            // 仕入伝票詳細を更新
            $sort_index = 1;
            $sort_numbers = [];
            foreach ($request->detail ?? [] as $detail) {
                $detail['purchase_order_id'] = $target_data->id;
                $detail['quantity'] = $detail['quantity'] ?? 0;
                $detail['unit_name'] = $detail['unit_name'] ?? '';
                $detail['unit_price'] = $detail['unit_price'] ?? 0;

                $detail['sub_total'] = MathHelper::getRoundingValue($detail['quantity'] * $detail['unit_price'],
                    0, config('consts.default.common.sub_total_rounding_method'));

                $detail['consumption_tax_rate'] = $detail['consumption_tax_rate'] ?? 0;
                $detail['reduced_tax_flag'] = $detail['reduced_tax_flag'] ?? 0;
                $detail['rounding_method_id'] = RoundingMethodType::ROUND_OFF;
                $detail['sort'] = $sort_index;

                // 仕入伝票詳細を更新
                $target_data = $this->repository
                    ->updatePurchaseOrderDetails(
                        $target_data,
                        (new PurchaseOrderDetail($detail))->toArray()
                    );

                // 仕入伝票詳細のレコード削除の為、ソートNo取得
                $sort_numbers[] = $sort_index;

                ++$sort_index;
            }
            // 更新後に無いレコード削除
            $target_data = $this->repository
                ->deleteOrderDetailsForUpdate($target_data, $sort_numbers);

            $order_details = $target_data->purchaseOrderDetail()->get();
            foreach ($order_details ?? [] as $detail) {
                // 最終単価更新
                $this->repository->upsertUnitPrice($target_data->supplier_id, $detail);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        return [$error_flag, MessageHelper::getUpdateMessage($error_flag, $target_data->order_number_zero_fill, 'purchase_order')];
    }

    /**
     * 削除処理
     *
     * @param PurchaseOrder $target_data
     * @return array
     *
     * @throws Exception
     */
    public function destroy(PurchaseOrder $target_data): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $this->repository->deleteOrdersReceived($target_data);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        return [$error_flag, MessageHelper::getDestroyMessage($error_flag, $target_data->order_number_zero_fill, 'purchase_order')];
    }
}
