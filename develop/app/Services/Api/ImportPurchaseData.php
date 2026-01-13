<?php

namespace App\Services\Api;

use App\Consts\API\PosApiConst;
use App\Consts\DB\Master\MasterUnitConst;
use App\Enums\OrderStatus;
use App\Enums\RoundingMethodType;
use App\Enums\TaxCalcType;
use App\Enums\TaxType;
use App\Enums\TransactionType;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterSupplier;
use App\Models\Master\MasterUnit;
use App\Models\Trading\PurchaseOrder;
use App\Repositories\Trading\PurchaseOrderRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportPurchaseData
{
    public function __construct() {}

    /**
     * 仕入処理
     *
     * @param array $purchase_data
     * @return bool
     *
     * @throws Exception
     */
    public function setPurchaseOrder(array $purchase_data): bool
    {
        $info_msg = 'POS連携 仕入伝票：取込スキップ 取引形態=' . $purchase_data['torihiki_status']
            . ', 店舗コード=' . $purchase_data['store_no'] . ', 伝票番号=' . $purchase_data['order_id']
            . ', 更新日時=' . $purchase_data['update_date'] . ', 仕入先コード=' . $purchase_data['customer_id'];

        try {
            // 仕入先コード
            $pos_supplier_id = intval($purchase_data['customer_id'] ?? 0);
            if ($pos_supplier_id === 0) {
                $pos_supplier_id = PosApiConst::POS_SUPPLIER_GENERAL;
            }
            $m_supplier = MasterSupplier::query()
                ->where('code', $pos_supplier_id)
                ->first();
            if (empty($m_supplier)) {
                // 仕入先マスタに存在しないコードの場合は、取り込みしない
                Log::info($info_msg . ', 存在しない仕入先コード=' . $purchase_data['customer_id']);

                return true;
            }
            $supplier_id = $m_supplier['id'];

            $purchase_repository = new PurchaseOrderRepository(new PurchaseOrder());

            // 伝票番号
            $order_number = $purchase_data['order_id'];

            // 仕入年月日
            $purchase_date = new Carbon(
                substr($purchase_data['sales_date'], 0, 4)
                . '-' . substr($purchase_data['sales_date'], 4, 2)
                . '-' . substr($purchase_data['sales_date'], 6, 2)
            );

            // 事業所コード
            $m_office_facilities = MasterOfficeFacility::query()
                ->where('code', $purchase_data['store_no'])
                ->first();
            if (empty($m_office_facilities)) {
                // 事業所マスタに存在しないコードの場合は、取り込みしない
                Log::info($info_msg . ', 存在しない事業所コード=' . $purchase_data['store_no']);

                return true;
            }

            // 既存伝票がある場合はスキップ
            $order_data = PurchaseOrder::query()
                ->where('order_number', $order_number)
                ->where('office_facilities_id', $m_office_facilities['id'])
                ->first();
            if (!empty($order_data)) {
                Log::info($info_msg . ', 既存の伝票');

                return true;
            }

            // 取引種別 ("0"：現金　"1"：売掛 ⇒ 1：現金 / 2:掛)
            $transaction_type_id = TransactionType::WITH_CASH;
            if ($purchase_data['uriage_status'] === '1') {
                $transaction_type_id = TransactionType::ON_ACCOUNT;
            }

            // 税計算区分(現⇒伝票毎、掛⇒請求毎)
            $tax_calc_type_id = TaxCalcType::ORDER;
            if ($transaction_type_id === TransactionType::ON_ACCOUNT) {
                $tax_calc_type_id = TaxCalcType::BILLING;
            }

            // 税区分（外税のみ）
            $tax_type_id = TaxType::OUT_TAX;

            // 伝票データセット
            $purchase = [
                // 伝票番号
                'order_number' => $order_number,
                // 発注日付
                'order_date' => $purchase_date->format('Y/m/d'),
                // 状態
                'order_status' => OrderStatus::ORDERED,
                // 仕入先ID
                'supplier_id' => $supplier_id,
                // 部門ID
                'department_id' => $m_office_facilities['department_id'],
                // 事業所ID
                'office_facilities_id' => $m_office_facilities['id'],
                'tax_calc_type_id' => $tax_calc_type_id,    // 税計算区分
                // 取引種別ID
                'transaction_type_id' => $transaction_type_id,
                // 仕入分類
                'purchase_classification_id' => intval($purchase_data['torihiki_status']),
                // 仕入合計
                'purchase_total' => $purchase_data['total'],
                // 値引
                'discount' => 0,
                // 今回仕入額_通常税率_外税分
                'purchase_total_normal_out' => $purchase_data['not_reduced_tax_total'] ?? 0,
                // 今回仕入額_軽減税率_外税分
                'purchase_total_reduced_out' => $purchase_data['reduced_tax_total'] ?? 0,
                // 今回仕入額_通常税率_内税分
                'purchase_total_normal_in' => 0,
                // 今回仕入額_軽減税率_内税分
                'purchase_total_reduced_in' => 0,
                // 今回仕入額_非課税分
                'purchase_total_free' => 0,
                // 消費税額_通常税率_外税分
                'purchase_tax_normal_out' => $purchase_data['not_reduced_tax'] ?? 0,
                // 消費税額_軽減税率_外税分
                'purchase_tax_reduced_out' => $purchase_data['reduced_tax'] ?? 0,
                // 消費税額_通常税率_内税分
                'purchase_tax_normal_in' => 0,
                // 消費税額_軽減税率_内税分
                'purchase_tax_reduced_in' => 0,
                // POS連携データ
                'link_pos' => PosApiConst::POS_DATA,
                'updated_id' => PosApiConst::POS_DATA_CREATER,
                'created_at' => $purchase_data['create_date'],
                'updated_at' => $purchase_data['update_date'],
            ];

            // 仕入伝票の新規登録
            $purchase = $purchase_repository->createPurchaseOrder($purchase);
            $purchase_order_id = $purchase->id;

            // 仕入伝票詳細を登録
            $details = (array) $purchase_data['details'] ?? [];
            $this->setPurchaseOrderDetail($purchase_repository, $purchase_order_id, $purchase, $details, $tax_type_id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('【Error】ImportPurchaseData | setPurchaseOrder :　' . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * 仕入詳細処理
     *
     * @param PurchaseOrderRepository $purchase_repository
     * @param string $purchase_order_id
     * @param Model $purchase
     * @param array $details
     * @param int $tax_type_id
     * @return void
     *
     * @throws Exception
     */
    private function setPurchaseOrderDetail(PurchaseOrderRepository $purchase_repository,
        string $purchase_order_id,
        Model $purchase,
        array $details,
        int $tax_type_id)
    {
        $info_msg = 'POS連携 仕入伝票：取込スキップ';

        try {
            // 詳細情報の登録
            $insert_details = [];
            $sort_index = 1;

            foreach ($details as $detail) {
                // デバッグログ追加
                error_log('Detail type: ' . gettype($detail));
                error_log('Detail content: ' . print_r($detail, true));

                // stdClass オブジェクトを配列に変換
                $detail = (array) $detail;

                // 商品コードの存在チェック
                if (empty($detail['product_code'])) {
                    Log::info($info_msg . ', 商品コードが空です');

                    continue;
                }

                // 商品
                $product = MasterProduct::query()
                    ->where('code', $detail['product_code'])
                    ->first();

                if (empty($product)) {
                    Log::info($info_msg . ', 存在しない商品コード=' . $detail['product_code']);

                    continue;
                }

                // 単位
                $unit_name = MasterUnit::query()
                    ->where('id', MasterUnitConst::UNIT_ID_FIXED_VALUE)
                    ->first();

                // 数値の取得
                $quantity = intval($detail['quantity'] ?? 0);
                $price = intval($detail['price'] ?? 0);
                $tax_percent_value = intval($detail['tax_percent'] ?? 0);

                // 小計金額
                $sub_total = $quantity * $price;

                // 消費税率
                $tax_percent = $tax_percent_value / 100;

                // 小計税額(外税)
                $sub_total_tax = $sub_total * $tax_percent;
                if ($tax_type_id === TaxType::IN_TAX) {
                    $sub_total_tax = $sub_total / (1 + $tax_percent) * $tax_percent;
                }

                $data = [
                    // 仕入伝票ID
                    'purchase_order_id' => $purchase_order_id,
                    // 商品ID
                    'product_id' => $product['id'],
                    // 商品名
                    'product_name' => $product['name'],
                    // 数量
                    'quantity' => $quantity,
                    // 単位
                    'unit_name' => $unit_name['name'],
                    // 単価
                    'unit_price' => $price,
                    // 値引額
                    'discount' => $detail['discount'] ?? 0,
                    // 小計金額
                    'sub_total' => $sub_total,
                    // 小計税額
                    'sub_total_tax' => $sub_total_tax,
                    // 税区分
                    'tax_type_id' => $tax_type_id,
                    // 消費税率
                    'consumption_tax_rate' => $tax_percent_value,
                    // 軽減税率対象フラグ
                    'reduced_tax_flag' => $detail['reduced_tax_use_flg'] ?? 0,
                    // 消費税端数処理方法(POSは切上のみ ⇒ 1：切捨 / 2：切上 / 3：四捨五入)
                    'rounding_method_id' => RoundingMethodType::ROUND_UP,
                    // ソート
                    'sort' => $sort_index,
                ];

                // 登録データを格納
                $insert_details[] = $data;

                ++$sort_index;
            }

            // 仕入伝票詳細を登録
            $purchase_details = $purchase_repository->createPurchaseOrderDetails($purchase, $insert_details);

            // 入出庫処理
            (new ImportInventoryData())->setPurchaseOrder($purchase_details);

        } catch (Exception $e) {
            Log::error('【Error】ImportPurchaseData | setPurchaseOrderDetail :　' . $e->getMessage());
            throw $e;
        }
    }
}
