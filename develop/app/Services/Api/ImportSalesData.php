<?php

namespace App\Services\Api;

use App\Consts\API\PosApiConst;
use App\Enums\RoundingMethodType;
use App\Enums\SalesClassification;
use App\Enums\TaxCalcType;
use App\Enums\TaxType;
use App\Enums\TransactionType;
use App\Helpers\LogHelper;
use App\Models\Invoice\ChargeData;
use App\Models\Invoice\ChargeDataSalesOrder;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use App\Models\Sale\SalesOrder;
use App\Models\Sale\SalesOrderDetail;
use App\Repositories\Sale\OrderRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportSalesData
{
    /** 取込スキップフラグ */
    private bool $is_skip_import;

    /**
     * コンストラクタ
     *
     * @param bool $is_skip_import
     */
    public function __construct(bool $is_skip_import)
    {
        $this->is_skip_import = $is_skip_import;
    }

    /**
     * 売上処理
     *
     * @param array $order_data
     * @return bool
     *
     * @throws Exception
     */
    public function insertSalesOrder(array $order_data): bool
    {
        $this->is_skip_import = false;
        $info_msg = 'POS連携 売上伝票：取込スキップ 取引形態=' . $order_data['torihiki_status']
            . ', 店舗コード=' . $order_data['store_no'] . ', 伝票番号=' . $order_data['order_id']
            . ', 更新日時=' . $order_data['update_date'];

        try {
            // 得意先コード
            $pos_customer_id = intval($order_data['customer_id'] ?? 0);
            if ($pos_customer_id === 0) {
                $pos_customer_id = PosApiConst::POS_CUSTOMER_GENERAL;
            }
            if ($pos_customer_id > PosApiConst::POS_CUSTOMER_GENERAL
                && $pos_customer_id < PosApiConst::POS_CUSTOMER_WAKOHEN) {
                $pos_customer_id = PosApiConst::POS_CUSTOMER_GENERAL;
            }
            $m_customer = MasterCustomer::query()
                ->where('code', $pos_customer_id)
                ->first();
            if (empty($m_customer)) {
                // 得意先マスタに存在しないコードの場合は、取り込みしない
                Log::channel('pos_info')->info($info_msg . ', 存在しない得意先コード=' . $order_data['customer_id']);

                return $this->is_skip_import = true;
            }
            $customer_id = $m_customer->id;
            $customer_code = $m_customer->code;

            $order_repository = new OrderRepository(new SalesOrder());

            // 伝票日付
            $order_date = substr($order_data['sales_date'], 0, 4)
                . '-' . substr($order_data['sales_date'], 4, 2)
                . '-' . substr($order_data['sales_date'], 6, 2);
            $order_date = new Carbon($order_date);

            // 事業所コード
            $m_office_facilities = MasterOfficeFacility::query()
                ->where('code', $order_data['store_no'])
                ->first();

            // 既存伝票がある場合はスキップ
            $sales_order_data = SalesOrder::query()
                ->where('order_number', $order_data['order_id'])
                ->where('office_facilities_id', $m_office_facilities['id'])
                ->first();
            if (!empty($sales_order_data)) {
                Log::channel('pos_info')->info($info_msg . ', 既存の伝票');

                return $this->is_skip_import;
            }

            // 取引種別 ("0"：現金　"1"：売掛 ⇒ 1：現 / 2:掛)
            $transaction_type_id = TransactionType::WITH_CASH;
            if ($order_data['uriage_status'] === '1') {
                $transaction_type_id = TransactionType::ON_ACCOUNT;
            }

            // 返品時のマイナス反転
            $reverse_sign = 1;
            if ($order_data['torihiki_status'] == SalesClassification::CLASSIFICATION_RETURN) {
                $reverse_sign = -1;
            }

            // 商品券(値引額)取得
            $discount = 0;
            if (array_key_exists('payment', $order_data)) {
                foreach ($order_data['payment'] as $discount_data) {
                    if (!is_null($discount_data->payment_status)) {
                        $discount += $discount_data->amount ?? 0;
                    }
                }
            }

            // 税計算区分(現⇒伝票毎、掛⇒請求毎)
            $tax_calc_type_id = TaxCalcType::ORDER;
            if ($transaction_type_id === TransactionType::ON_ACCOUNT) {
                $tax_calc_type_id = TaxCalcType::BILLING;
            }

            // 税区分(現⇒外税、掛⇒得意先コード 1～99 or 100000以上ならば内税、それ以外は外税)
            $tax_type_id = TaxType::OUT_TAX;
            if ($transaction_type_id === TransactionType::ON_ACCOUNT
               && (($customer_code >= 1 && $customer_code <= 99) || $customer_code >= 100000)) {
                $tax_type_id = TaxType::IN_TAX;
            }

            // 伝票データセット
            $order = [
                'order_number' => $order_data['order_id'],  // 伝票番号
                'orders_received_number' => null,  // 受注番号 nullable
                'order_date' => $order_date->format('Y/m/d'),  // 伝票日付
                'billing_date' => $order_date->format('Y/m/d'),  // 請求日
                'department_id' => $m_office_facilities['department_id'],  // 部門ID
                'office_facilities_id' => $m_office_facilities['id'],  // 事業所ID
                'customer_id' => $customer_id,  // 得意先ID
                'billing_customer_id' => $customer_id,  // 請求先ID
                'branch_id' => null,  // 支所ID nullable
                'recipient_id' => null,  // 納品先ID nullable
                'tax_calc_type_id' => $tax_calc_type_id,    // 税計算区分
                'transaction_type_id' => $transaction_type_id,  // 取引種別ID
                'sales_classification_id' => intval($order_data['torihiki_status']),  // 売上分類 ("0"：販売　"1"：返品)

                // 売上合計(外税:税抜) = POS(total) - POS(total_tax)
                'sales_total' => $reverse_sign * (intval($order_data['total'])),  // todo (商品値引は反映、伝票値引は反映されていない状態ならOK)
                'discount' => $discount,  // 伝票値引 ORDER_PAYMENT_DATA の amount 集計値
                'sales_total_normal_out' => $reverse_sign * $order_data['not_reduced_tax_total'] ?? 0,  // 今回売上額_通常税率_外税分 default:0
                'sales_total_reduced_out' => $reverse_sign * $order_data['reduced_tax_total'] ?? 0,  // 今回売上額_軽減税率_外税分 default:0
                'sales_total_normal_in' => 0,  // 今回売上額_通常税率_内税分 default:0
                'sales_total_reduced_in' => 0,  // 今回売上額_軽減税率_内税分 default:0
                'sales_total_free' => 0,  // 今回売上額_非課税分 default:0
                'sales_tax_normal_out' => $reverse_sign * $order_data['not_reduced_tax'] ?? 0,  // 消費税額_通常税率_外税分 default:0
                'sales_tax_reduced_out' => $reverse_sign * $order_data['reduced_tax'] ?? 0,  // 消費税額_軽減税率_外税分 default:0
                'sales_tax_normal_in' => 0,  // 消費税額_通常税率_内税分 default:0
                'sales_tax_reduced_in' => 0,  // 消費税額_軽減税率_内税分 default:0
                'closing_at' => null,  // 締処理日時
                'printing_date' => null,  // 納品書出力日
                'memo' => $order_data['note'],  // メモ
                'link_pos' => PosApiConst::POS_DATA,  // POS連携データ
                'creator_id' => PosApiConst::POS_DATA_CREATER,
                'updated_id' => PosApiConst::POS_DATA_CREATER,
                'created_at' => $order_data['create_date'],
                'updated_at' => $order_data['update_date'],
            ];

            // 伝票を登録
            $order = $order_repository->createSalesOrder($order);

            // 伝票金額の集計用
            $sales_total_normal_out = 0;    // 売上額_通常税率_外税分
            $sales_total_reduced_out = 0;   // 売上額_軽減税率_外税分
            $sales_total_normal_in = 0;     // 売上額_通常税率_内税分
            $sales_total_reduced_in = 0;    // 売上額_軽減税率_内税分
            $sales_total_free = 0;          // 今回売上額_非課税分
            $sales_tax_normal_out = 0;      // 消費税額_通常税率_外税分
            $sales_tax_reduced_out = 0;     // 消費税額_軽減税率_外税分
            $sales_tax_normal_in = 0;       // 消費税額_通常税率_内税分
            $sales_tax_reduced_in = 0;      // 消費税額_軽減税率_内税分

            // 詳細データセット
            $sort_index = 1;
            $sort_numbers = [];
            $sales_total = 0;
            $del_flg = false;
            foreach ((array) $order_data['details'] ?? [] as $detail) {
                $detail = (array) $detail;
                // 商品
                $m_product = MasterProduct::query()
                    ->where('code', $detail['product_code'])
                    ->first();
                if (empty($m_product)) {
                    // 取込スキップフラグ
                    $this->is_skip_import = true;
                    Log::channel('pos_info')->info($info_msg . ', 存在しない商品コード=' . $detail['product_code']);
                    $del_flg = true;

                    continue;
                }

                // 単位
                $unit_name = $m_product->getProductUnitNameAttribute();

                // 値引(正の数へ変換)
                $discount = abs(intval($detail['discount'] ?? 0));

                // 小計金額
                $sales_total += $sub_total = intval($detail['quantity'] ?? 0) * intval($detail['price'] ?? 0) - $discount;

                // 消費税率
                $tax_percent = intval($detail['tax_percent'] ?? 0) / 100;

                // 小計税額(外税)
                $sub_total_tax = $sub_total * $tax_percent;
                if ($tax_type_id === TaxType::IN_TAX) {
                    $sub_total_tax = $sub_total / (1 + $tax_percent) * $tax_percent;
                }

                $order_detail = [
                    'sales_order_id' => $order->id,  // 売上伝票ID
                    'product_id' => $m_product['id'],  // 商品ID
                    'product_name' => $detail['product_name'],  // 商品名
                    'unit_price_decimal_digit' => $m_product['unit_price_decimal_digit'],  // 単価小数桁数 default:0
                    'quantity_decimal_digit' => $m_product['quantity_decimal_digit'],  // 数量小数桁数 default:0
                    'quantity_rounding_method_id' => $m_product['quantity_rounding_method_id'],  // 数量端数処理
                    'amount_rounding_method_id' => $m_product['amount_rounding_method_id'],  // 金額端数処理
                    'quantity' => $detail['quantity'],  // 数量
                    'unit_name' => $unit_name,  // 単位
                    'unit_price' => $detail['price'],  // 単価
                    'discount' => $discount,  // 値引額

                    // 小計金額
                    'sub_total' => $sub_total,
                    // 小計税額
                    'sub_total_tax' => $sub_total_tax,

                    'tax_type_id' => $tax_type_id,  // 税区分
                    'purchase_unit_price' => $m_product['purchase_unit_price'],  // 仕入単価
                    'consumption_tax_rate' => $detail['tax_percent'] ?? 0,  // 消費税率
                    'reduced_tax_flag' => $detail['reduced_tax_use_flg'] ?? 0,  // 軽減税率対象フラグ
                    // 消費税端数処理方法 POSは切上のみ ⇒ 1：切捨 / 2：切上 / 3：四捨五入
                    'rounding_method_id' => RoundingMethodType::ROUND_UP,
                    // 粗利POSは切上で計算
                    'gross_profit' => ceil(intval($detail['quantity']) * intval($detail['price']))
                        - ceil(intval($detail['quantity']) * intval($m_product['purchase_unit_price'])),
                    'note' => $detail['note'],  // 備考
                    'sort' => $sort_index,  // ソート
                    'created_at' => $order_data['create_date'],
                    'updated_at' => $order_data['update_date'],
                ];

                // 詳細を更新
                $order = $order_repository
                    ->updateSalesOrderDetails($order, new SalesOrderDetail($order_detail)->toArray());

                // 売上伝票詳細のレコード削除の為、ソートNoを退避
                $sort_numbers[] = $sort_index;

                // 伝票金額の内訳を集計
                if (intval($detail['tax_percent'] ?? 0) === 0) {
                    // 非課税分
                    $sales_total_free += $sub_total;
                    ++$sort_index;

                    continue;
                }
                if ($tax_type_id === TaxType::OUT_TAX
                    && intval($detail['reduced_tax_use_flg'] ?? 0) === 0) {
                    // 売上額_通常税率_外税分
                    $sales_total_normal_out += $sub_total;
                    // 消費税額_通常税率_外税分
                    $sales_tax_normal_out += $sub_total_tax;
                    ++$sort_index;

                    continue;
                }
                if ($tax_type_id === TaxType::OUT_TAX
                    && intval($detail['reduced_tax_use_flg'] ?? 0) === 1) {
                    // 売上額_軽減税率_外税分
                    $sales_total_reduced_out += $sub_total;
                    // 消費税額_通常税率_外税分
                    $sales_tax_reduced_out += $sub_total_tax;
                    ++$sort_index;

                    continue;
                }
                if ($tax_type_id === TaxType::IN_TAX
                    && intval($detail['reduced_tax_use_flg'] ?? 0) === 0) {
                    // 売上額_通常税率_内税分
                    $sales_total_normal_in += $sub_total;
                    // 消費税額_通常税率_内税分
                    $sales_tax_normal_in += $sub_total_tax;
                    ++$sort_index;

                    continue;
                }
                if ($tax_type_id === TaxType::IN_TAX
                    && intval($detail['reduced_tax_use_flg'] ?? 0) === 1) {
                    // 売上額_軽減税率_内税分
                    $sales_total_reduced_in += $sub_total;
                    // 消費税額_軽減税率_内税分
                    $sales_tax_reduced_in += $sub_total_tax;
                    ++$sort_index;

                    continue;
                }
            }

            if (!$del_flg) {
                // 更新に無かった既存レコードを削除
                if (!empty($sort_numbers)) {
                    $order = $order_repository->deleteOrderDetailsForUpdate($order, $sort_numbers);
                }

                // 最終単価更新
                $order_details = $order->salesOrderDetail()->get();
                foreach ($order_details ?? [] as $detail) {
                    $order_repository->upsertCustomerPrice($order->customer_id, $order->order_date, $detail);
                }
                DB::commit();
            }

            if ($del_flg) {
                // 売上詳細でスキップがあった場合の売上伝票削除
                $order->delete();
                DB::commit();

                return $this->is_skip_import;
            }

            // 伝票金額内訳更新
            $model = SalesOrder::query()
                ->where('order_number', $order['order_number'])
                ->where('office_facilities_id', $order['office_facilities_id'])
                ->first();
            $order = [
                'sales_total' => $sales_total,
                'sales_total_normal_out' => $sales_total_normal_out,
                'sales_total_reduced_out' => $sales_total_reduced_out,
                'sales_total_normal_in' => $sales_total_normal_in,
                'sales_total_reduced_in' => $sales_total_reduced_in,
                'sales_total_free' => $sales_total_free,
                'sales_tax_normal_out' => $sales_tax_normal_out,
                'sales_tax_reduced_out' => $sales_tax_reduced_out,
                'sales_tax_normal_in' => $sales_tax_normal_in,
                'sales_tax_reduced_in' => $sales_tax_reduced_in,
            ];
            $order = $order_repository->updateSalesOrder($model, $order);

            DB::commit();

            // 入出庫処理
            (new ImportInventoryData())->setSalesOrder($order);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('【Error】setSalesOrder :　' . $e->getMessage());
        }

        // 取込スキップフラグ
        return $this->is_skip_import;
    }

    /**
     * 売上取消処理
     *
     * @param array $order_data
     * @return bool
     *
     * @throws Exception
     */
    public function deleteSalesOrder(array $order_data): bool
    {
        $this->is_skip_import = false;
        $order_repository = new OrderRepository(new SalesOrder());

        // 事業所コード
        $m_office_facilities = MasterOfficeFacility::query()
            ->where('code', $order_data['store_no'])
            ->first();
        $department_id = $m_office_facilities['department_id'];
        $office_facilities_id = $m_office_facilities['id'];

        // 取消対象データ取得
        $sales_order = SalesOrder::query()
            ->where('order_number', $order_data['order_id'])    // 伝票番号
            ->where('department_id', $department_id)
            ->where('office_facilities_id', $office_facilities_id)
            ->first();
        if (empty($sales_order)) {
            Log::channel('pos_info')->info('POS連携 売上取消：取消対象が存在しない 伝票番号=' . $order_data['order_id']
                . ', 事業所コード=' . $office_facilities_id);

            // 取込スキップフラグ
            return $this->is_skip_import;
        }

        $error_flag = false;
        DB::beginTransaction();
        try {
            $sales_order = $order_repository->deleteSalesOrder($sales_order);

            /** 請求データ_売上伝票リレーション削除 */
            $charge_data_sales_order = ChargeDataSalesOrder::query()
                ->where('sales_order_id', $sales_order->id)
                ->first();
            if ($charge_data_sales_order != null) {
                $charge_data_sales_order->delete();

                /** 請求データ修正 */
                $charge_data = ChargeData::query()
                    ->where('id', $charge_data_sales_order->charge_data_id)
                    ->first();
                $charge_data->sales_total -= $sales_order->sales_total; // 売上合計
                $charge_data->charge_total = $charge_data->calculated_charge_total; // 今回請求額
                $charge_data->save();
            }

            DB::commit();

            // 入出庫処理
            (new ImportInventoryData())->setSalesOrder($sales_order, true);

        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // 取込スキップフラグ
        return $this->is_skip_import;
    }
}
