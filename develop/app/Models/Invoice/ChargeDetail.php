<?php

/**
 * 請求伝票明細モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Invoice;

use App\Consts\DB\Master\MasterProductsConst;
use App\Consts\DB\Sale\DepositOrderConst;
use App\Consts\DB\Sale\SalesOrderConst;
use App\Enums\OrderType;
use App\Enums\TransactionType;
use App\Models\Sale\DepositOrder;
use App\Models\Sale\SalesOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 請求伝票明細モデル
 */
class ChargeDetail extends Model
{
    // region static method

    /**
     * 伝票データ取得
     *
     * @param array $sales_order_ids
     * @param array $deposit_order_ids
     * @return Collection
     */
    public static function getOrder(array $sales_order_ids, array $deposit_order_ids): Collection
    {
        $order_number_maxlength = SalesOrderConst::ORDER_NUMBER_MAX_LENGTH;
        $deposit_number_maxlength = DepositOrderConst::ORDER_NUMBER_MAX_LENGTH;
        $product_code_maxlength = MasterProductsConst::CODE_MAX_LENGTH;

        // 入金伝票用カラム
        $arr_select_column_order_deposit = [
            /** 伝票日付 */
            'deposit_orders.order_date',
            /** 伝票ID */
            DB::raw('deposit_orders.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(deposit_orders.order_number, {$deposit_number_maxlength}, '0') AS order_number"),
            /** 伝票種別 */
            DB::raw(OrderType::DEPOSIT . ' AS order_type'),
            /** 得意先名 */
            'm_customers.name AS name',
            /** 支所名 */
            DB::raw("'' AS b_name"),
            /** 商品コード */
            DB::raw("'' AS product_code"),
            /** 商品名 */
            DB::raw("'' AS product_name"),
            /** 数量 */
            DB::raw("'' AS quantity"),
            /** 数量小数桁数 */
            DB::raw('0 AS quantity_decimal_digit'),
            /** 単位 */
            DB::raw("'' AS unit_name"),
            /** 単価 */
            DB::raw("'' AS unit_price"),
            /** 単価小数桁数 */
            DB::raw('0 AS unit_price_decimal_digit'),
            /** 売上 */
            DB::raw("'' AS sub_total"),
            /** 入金 */
            DB::raw('deposit_orders.deposit AS deposit_total'),
            /** 備考 */
            DB::raw('deposit_orders.note AS detail_note'),
            /** ソート */
            DB::raw("'' AS sort"),
            /** 現金 */
            DB::raw('deposit_order_details.amount_cash AS amount_cash'),
            /** 小切手 */
            DB::raw('deposit_order_details.amount_check AS amount_check'),
            /** 振込 */
            DB::raw('deposit_order_details.amount_transfer AS amount_transfer'),
            /** 手形 */
            DB::raw('deposit_order_details.amount_bill AS amount_bill'),
            /** 相殺 */
            DB::raw('deposit_order_details.amount_offset AS amount_offset'),
            /** 値引 */
            DB::raw('deposit_order_details.amount_discount AS amount_discount'),
            /** 手数料 */
            DB::raw('deposit_order_details.amount_fee AS amount_fee'),
            /** その他*/
            DB::raw('deposit_order_details.amount_other AS amount_other'),
        ];

        $order_deposit = DepositOrder::select($arr_select_column_order_deposit)
            ->join('deposit_order_details', function ($join) {
                $join->on(
                    'deposit_orders.id',
                    '=',
                    'deposit_order_details.deposit_order_id'
                )
                    ->whereNull('deposit_order_details.deleted_at');
            })
            ->leftJoin('m_customers', 'deposit_orders.customer_id', '=', 'm_customers.id')
            ->whereIn('deposit_orders.id', $deposit_order_ids);

        // 売上伝票明細用カラム
        $arr_select_column_order_detail = [
            /** 伝票日付 */
            'sales_orders.order_date',
            /** 伝票ID */
            DB::raw('sales_orders.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(sales_orders.order_number, {$order_number_maxlength}, '0') AS order_number"),
            /** 伝票種別 */
            DB::raw(OrderType::ACCOUNTS_RECEIVABLE . ' AS order_type'),
            /** 得意先名 */
            'm_customers.name AS name',
            /** 支所名 */
            'm_branches.name AS b_name',
            /** 商品コード */
            DB::raw("LPAD(m_products.code, {$product_code_maxlength}, '0') AS product_code"),
            /** 商品名 */
            'sales_order_details.product_name',
            /** 数量 */
            'sales_order_details.quantity',
            /** 数量小数桁数 */
            'm_products.quantity_decimal_digit',
            /** 単位 */
            'sales_order_details.unit_name',
            /** 単価 */
            'sales_order_details.unit_price',
            /** 単価小数桁数 */
            'm_products.unit_price_decimal_digit',
            /** 売上 */
            DB::raw('(sales_order_details.quantity * sales_order_details.unit_price) AS sub_total'),
            /** 入金 */
            DB::raw("'' AS deposit_total"),
            /** 備考 */
            DB::raw('sales_order_details.note AS detail_note'),
            /** ソート */
            DB::raw('sales_order_details.sort'),
            /** 現金 */
            DB::raw("'' AS amount_cash"),
            /** 小切手 */
            DB::raw("'' AS amount_check"),
            /** 振込 */
            DB::raw("'' AS amount_transfer"),
            /** 手形 */
            DB::raw("'' AS amount_bill"),
            /** 相殺 */
            DB::raw("'' AS amount_offset"),
            /** 値引 */
            DB::raw("'' AS amount_discount"),
            /** 手数料 */
            DB::raw("'' AS amount_fee"),
            /** その他*/
            DB::raw("'' AS amount_other"),
        ];

        // 伝票データ
        return SalesOrder::select($arr_select_column_order_detail)
            ->join('sales_order_details', function ($join) {
                $join->on(
                    'sales_orders.id',
                    '=',
                    'sales_order_details.sales_order_id'
                )
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->whereIn('sales_orders.id', $sales_order_ids)
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_products_units', 'm_products.id', '=', 'm_products_units.product_id')
            ->leftJoin('m_customers', 'sales_orders.customer_id', '=', 'm_customers.id')
            ->leftJoin('m_branches', 'sales_orders.branch_id', '=', 'm_branches.id')
            ->where('sales_orders.transaction_type_id', TransactionType::ON_ACCOUNT)
            ->unionAll($order_deposit)
            ->oldest('order_number')
            ->oldest('order_date')
            ->oldest('sort')
            ->get();
    }

    /**
     * 伝票データ取得
     *
     * @param ChargeData $charge_data
     * @return Collection
     */
    public static function getOrderDetail(ChargeData $charge_data): Collection
    {
        $charge_id = $charge_data->id;
        $department_id = $charge_data->department_id;
        $office_facilities_id = $charge_data->office_facilities_id;
        $order_number_maxlength = SalesOrderConst::ORDER_NUMBER_MAX_LENGTH;
        $deposit_number_maxlength = DepositOrderConst::ORDER_NUMBER_MAX_LENGTH;
        $product_code_maxlength = MasterProductsConst::CODE_MAX_LENGTH;

        // 入金伝票用カラム
        $arr_select_column_deposit_order_detail = [
            /** 得意先ID */
            'deposit_orders.customer_id',
            /** 得意先名 */
            DB::raw('m_customers.name AS customer_name'),
            /** 伝票日付 */
            'deposit_orders.order_date',
            /** 伝票ID */
            DB::raw('deposit_orders.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(deposit_orders.order_number, {$deposit_number_maxlength}, '0') AS order_number"),
            /** 伝票種別 */
            DB::raw(OrderType::DEPOSIT . ' AS order_type'),
            /** 商品コード */
            DB::raw("'' AS product_code"),
            /** 商品名 */
            DB::raw("'' AS product_name"),
            /** 支所ID */
            DB::raw("'' AS branch_id"),
            /** 支所名 */
            DB::raw("'' AS b_name"),
            /** 支所名略称 */
            DB::raw("'' AS mnemonic_name"),
            /** 税タイプ */
            DB::raw("'' AS tax_type_id"),
            /** 税率 */
            DB::raw('0 AS consumption_tax_rate'),
            /** 税端数処理方法 */
            DB::raw("'' AS rounding_method_id"),
            /** 数量 */
            DB::raw("'' AS quantity"),
            /** 数量小数桁数 */
            DB::raw('0 AS quantity_decimal_digit'),
            /** 単位 */
            DB::raw("'' AS unit_name"),
            /** 単価 */
            DB::raw("'' AS unit_price"),
            /** 単価小数桁数 */
            DB::raw('0 AS unit_price_decimal_digit'),
            /** 売上 */
            DB::raw("'' AS sub_total"),
            /** 入金 */
            DB::raw('deposit_orders.deposit AS deposit_total'),
            /** 備考 */
            DB::raw('deposit_orders.note AS detail_note'),
            /** ソート */
            DB::raw('0 AS sort'),
            /** 金額_現金 */
            DB::raw('deposit_order_details.amount_cash AS amount_cash'),
            /** 備考_現金 */
            DB::raw('deposit_order_details.note_cash AS note_cash'),
            /** 金額_小切手 */
            DB::raw('deposit_order_details.amount_check AS amount_check'),
            /** 備考_小切手 */
            DB::raw('deposit_order_details.note_check AS note_check'),
            /** 金額_振込 */
            DB::raw('deposit_order_details.amount_transfer AS amount_transfer'),
            /** 備考_振込 */
            DB::raw('deposit_order_details.note_transfer AS note_transfer'),
            /** 金額_手形 */
            DB::raw('deposit_order_details.amount_bill AS amount_bill'),
            /** 備考_手形 */
            DB::raw('deposit_order_details.note_bill AS note_bill'),
            /** 金額_相殺 */
            DB::raw('deposit_order_details.amount_offset AS amount_offset'),
            /** 備考_相殺 */
            DB::raw('deposit_order_details.note_offset AS note_offset'),
            /** 金額_値引 */
            DB::raw('deposit_order_details.amount_discount AS amount_discount'),
            /** 備考_値引 */
            DB::raw('deposit_order_details.note_discount AS note_discount'),
            /** 金額_手数料 */
            DB::raw('deposit_order_details.amount_fee AS amount_fee'),
            /** 備考_手数料 */
            DB::raw('deposit_order_details.note_fee AS note_fee'),
            /** 金額_その他 */
            DB::raw('deposit_order_details.amount_other AS amount_other'),
            /** 備考_その他 */
            DB::raw('deposit_order_details.note_other AS note_other'),
            /** 手形_手形期日 */
            DB::raw('deposit_order_bill.bill_date AS bill_date'),
            /** 手形_手形番号 */
            DB::raw('deposit_order_bill.bill_number AS bill_number'),
        ];

        $order_deposit = DepositOrder::select($arr_select_column_deposit_order_detail)
            ->join('charge_data_deposit_order', function ($join) {
                $join->on(
                    'deposit_orders.id',
                    '=',
                    'charge_data_deposit_order.deposit_order_id'
                )
                    ->whereNull('deposit_orders.deleted_at');
            })
            ->where('charge_data_deposit_order.charge_data_id', $charge_id)
            ->where('deposit_orders.department_id', $department_id)
            ->where('deposit_orders.office_facilities_id', $office_facilities_id)
            ->Join('m_customers', 'deposit_orders.customer_id', '=', 'm_customers.id')
            ->join('deposit_order_details', function ($join) {
                $join->on(
                    'deposit_orders.id',
                    '=',
                    'deposit_order_details.deposit_order_id'
                )
                    ->whereNull('deposit_order_details.deleted_at');
            })
            ->leftjoin('deposit_order_bill', function ($join) {
                $join->on(
                    'deposit_orders.id',
                    '=',
                    'deposit_order_bill.deposit_order_id'
                );
            });

        // 売上伝票明細用カラム
        $arr_select_column_sales_order_detail = [
            /** 得意先ID */
            'sales_orders.customer_id',
            /** 得意先名 */
            DB::raw('m_customers.name AS customer_name'),
            /** 伝票日付 */
            'sales_orders.order_date',
            /** 伝票ID */
            DB::raw('sales_orders.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(sales_orders.order_number, {$order_number_maxlength}, '0') AS order_number"),
            /** 伝票種別 */
            DB::raw(OrderType::ACCOUNTS_RECEIVABLE . ' AS order_type'),
            /** 商品コード */
            DB::raw("LPAD(m_products.code, {$product_code_maxlength}, '0') AS product_code"),
            /** 商品名 */
            'sales_order_details.product_name',
            /** 支所ID */
            'sales_orders.branch_id',
            /** 支所名 */
            'm_branches.name AS b_name',
            /** 支所名略称 */
            'm_branches.mnemonic_name AS mnemonic_name',
            /** 税タイプ */
            'sales_order_details.tax_type_id',
            /** 税率 */
            'sales_order_details.consumption_tax_rate',
            /** 税端数処理方法 */
            'sales_order_details.rounding_method_id',
            /** 数量 */
            'sales_order_details.quantity',
            /** 数量小数桁数 */
            'm_products.quantity_decimal_digit',
            /** 単位 */
            'sales_order_details.unit_name',
            /** 単価 */
            'sales_order_details.unit_price',
            /** 単価小数桁数 */
            'm_products.unit_price_decimal_digit',
            /** 売上 */
            DB::raw('(sales_order_details.quantity * sales_order_details.unit_price) AS sub_total'),
            /** 入金 */
            DB::raw("'' AS deposit_total"),
            /** 備考 */
            DB::raw('sales_order_details.note AS detail_note'),
            /** ソート */
            DB::raw('sales_order_details.sort AS sort'),
            /** 金額_現金 */
            DB::raw('0 AS amount_cash'),
            /** 備考_現金 */
            DB::raw("'' AS note_cash"),
            /** 金額_小切手 */
            DB::raw('0 AS amount_check'),
            /** 備考_小切手 */
            DB::raw("'' AS note_check"),
            /** 金額_振込 */
            DB::raw('0 AS amount_transfer'),
            /** 備考_振込 */
            DB::raw("'' AS note_transfer"),
            /** 金額_手形 */
            DB::raw('0 AS amount_bill'),
            /** 備考_手形 */
            DB::raw("'' AS note_bill"),
            /** 金額_相殺 */
            DB::raw('0 AS amount_offset'),
            /** 備考_相殺 */
            DB::raw("'' AS note_offset"),
            /** 金額_値引 */
            DB::raw('0 AS amount_discount'),
            /** 備考_値引 */
            DB::raw("'' AS note_discount"),
            /** 金額_手数料 */
            DB::raw('0 AS amount_fee'),
            /** 備考_手数料 */
            DB::raw("'' AS note_fee"),
            /** 金額_その他 */
            DB::raw('0 AS amount_other'),
            /** 備考_その他 */
            DB::raw("'' AS note_other"),
            /** 手形_手形期日 */
            DB::raw("'' AS bill_date"),
            /** 手形_手形番号 */
            DB::raw("'' AS bill_number"),
        ];

        // 伝票データ
        return SalesOrder::select($arr_select_column_sales_order_detail)
            ->join('charge_data_sales_order', function ($join) {
                $join->on(
                    'sales_orders.id',
                    '=',
                    'charge_data_sales_order.sales_order_id'
                )
                    ->whereNull('sales_orders.deleted_at');
            })
            ->where('charge_data_sales_order.charge_data_id', $charge_id)
            ->where('sales_orders.department_id', $department_id)
            ->where('sales_orders.office_facilities_id', $office_facilities_id)
            ->where('sales_orders.transaction_type_id', TransactionType::ON_ACCOUNT)
            ->Join('m_customers', 'sales_orders.customer_id', '=', 'm_customers.id')
            ->join('sales_order_details', function ($join) {
                $join->on(
                    'sales_orders.id',
                    '=',
                    'sales_order_details.sales_order_id'
                )
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_products_units', 'm_products.id', '=', 'm_products_units.product_id')
            ->leftJoin('m_branches', 'sales_orders.branch_id', '=', 'm_branches.id')
            ->unionAll($order_deposit)
            ->oldest('customer_id')
            ->orderBy('order_type', 'desc')
            ->oldest('branch_id')
            ->oldest('order_date')
            ->oldest('order_number')
            ->oldest('sort')
            ->get();
    }

    /**
     * 得意先別伝票データ明細取得
     *
     * @param int $charge_id 請求データID
     * @param int $billing_customer_id 請求先ID
     * @return Collection
     */
    public static function getOrderBillingCustomer(int $charge_id, int $billing_customer_id): Collection
    {

        // 入金伝票用カラム
        $arr_select_column_deposit_order_detail = [
            /** 得意先ID */
            DB::raw('deposit_orders.customer_id AS customer_id'),
        ];

        $order_deposit = DepositOrder::select($arr_select_column_deposit_order_detail)
            ->join('charge_data_deposit_order', function ($join) {
                $join->on(
                    'deposit_orders.id',
                    '=',
                    'charge_data_deposit_order.deposit_order_id'
                )
                    ->whereNull('deposit_orders.deleted_at');
            })
            ->where('charge_data_deposit_order.charge_data_id', $charge_id)
            ->where('deposit_orders.customer_id', '<>', $billing_customer_id);

        // 売上伝票明細用カラム
        $arr_select_column_sales_order_detail = [
            /** 得意先ID */
            DB::raw('sales_orders.customer_id AS customer_id'),
        ];

        // 伝票データ
        return SalesOrder::select($arr_select_column_sales_order_detail)
            ->join('charge_data_sales_order', function ($join) {
                $join->on(
                    'sales_orders.id',
                    '=',
                    'charge_data_sales_order.sales_order_id'
                )
                    ->whereNull('sales_orders.deleted_at');
            })
            ->where('charge_data_sales_order.charge_data_id', $charge_id)
            ->where('transaction_type_id', TransactionType::ON_ACCOUNT)
            ->where('sales_orders.customer_id', '<>', $billing_customer_id)
            ->unionAll($order_deposit)
            ->oldest('customer_id')
            ->get();
    }

    /**
     * 得意先別伝票データ明細取得
     *
     * @param int $charge_id 請求データID
     * @return Collection
     */
    public static function getOrderBillingCustomerDetail(int $charge_id): Collection
    {
        $order_number_maxlength = SalesOrderConst::ORDER_NUMBER_MAX_LENGTH;
        $deposit_number_maxlength = DepositOrderConst::ORDER_NUMBER_MAX_LENGTH;
        $product_code_maxlength = MasterProductsConst::CODE_MAX_LENGTH;

        // 入金伝票用カラム
        $arr_select_column_deposit_order_detail = [
            /** 得意先ID */
            'deposit_orders.customer_id',
            /** 得意先名 */
            'm_customers.name',
            /** 伝票日付 */
            'deposit_orders.order_date',
            /** 伝票ID */
            DB::raw('deposit_orders.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(deposit_orders.order_number, {$deposit_number_maxlength}, '0') AS order_number"),
            /** 伝票種別 */
            DB::raw(OrderType::DEPOSIT . ' AS order_type'),
            /** 商品コード */
            DB::raw("'' AS product_code"),
            /** 商品名 */
            DB::raw("'' AS product_name"),
            /** 税タイプ */
            DB::raw("'' AS tax_type_id"),
            /** 税率 */
            DB::raw('0 AS consumption_tax_rate'),
            /** 税端数処理方法 */
            DB::raw("'' AS rounding_method_id"),
            /** 数量 */
            DB::raw("'' AS quantity"),
            /** 数量小数桁数 */
            DB::raw('0 AS quantity_decimal_digit'),
            /** 単位 */
            DB::raw("'' AS unit_name"),
            /** 単価 */
            DB::raw("'' AS unit_price"),
            /** 単価小数桁数 */
            DB::raw('0 AS unit_price_decimal_digit'),
            /** 売上 */
            DB::raw("'' AS sub_total"),
            /** 入金 */
            DB::raw('deposit_orders.deposit AS deposit_total'),
            /** 備考 */
            DB::raw('deposit_orders.note AS detail_note'),
            /** ソート */
            DB::raw('0 AS sort'),
        ];

        $order_deposit = DepositOrder::select($arr_select_column_deposit_order_detail)
            ->join('charge_data_deposit_order', function ($join) {
                $join->on(
                    'deposit_orders.id',
                    '=',
                    'charge_data_deposit_order.deposit_order_id'
                )
                    ->whereNull('deposit_orders.deleted_at');
            })
            ->join('m_customers', 'deposit_orders.customer_id', '=', 'm_customers.id')
            ->where('charge_data_deposit_order.charge_data_id', $charge_id)
            ->join('deposit_order_details', function ($join) {
                $join->on(
                    'deposit_orders.id',
                    '=',
                    'deposit_order_details.deposit_order_id'
                )
                    ->whereNull('deposit_order_details.deleted_at');

            });

        // 売上伝票明細用カラム
        $arr_select_column_sales_order_detail = [
            /** 得意先ID */
            'sales_orders.customer_id',
            /** 得意先名 */
            'm_customers.name',
            /** 伝票日付 */
            'sales_orders.order_date',
            /** 伝票ID */
            DB::raw('sales_orders.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(sales_orders.order_number, {$order_number_maxlength}, '0') AS order_number"),
            /** 伝票種別 */
            DB::raw(OrderType::ACCOUNTS_RECEIVABLE . ' AS order_type'),
            /** 商品コード */
            DB::raw("LPAD(m_products.code, {$product_code_maxlength}, '0') AS product_code"),
            /** 商品名 */
            'sales_order_details.product_name',
            /** 税タイプ */
            'sales_order_details.tax_type_id',
            /** 税率 */
            'sales_order_details.consumption_tax_rate',
            /** 税端数処理方法 */
            'sales_order_details.rounding_method_id',
            /** 数量 */
            'sales_order_details.quantity',
            /** 数量小数桁数 */
            'm_products.quantity_decimal_digit',
            /** 単位 */
            'sales_order_details.unit_name',
            /** 単価 */
            'sales_order_details.unit_price',
            /** 単価小数桁数 */
            'm_products.unit_price_decimal_digit',
            /** 売上 */
            DB::raw('sales_order_details.sub_total AS sub_total'),
            /** 入金 */
            DB::raw("'' AS deposit_total"),
            /** 備考 */
            DB::raw('sales_order_details.note AS detail_note'),
            /** ソート */
            DB::raw('sales_order_details.sort AS sort'),
        ];

        // 伝票データ
        return SalesOrder::select($arr_select_column_sales_order_detail)
            ->join('charge_data_sales_order', function ($join) {
                $join->on(
                    'sales_orders.id',
                    '=',
                    'charge_data_sales_order.sales_order_id'
                )
                    ->whereNull('sales_orders.deleted_at');
            })
            ->where('charge_data_sales_order.charge_data_id', $charge_id)
            ->join('m_customers', 'deposit_orders.customer_id', '=', 'm_customers.id')
            ->join('sales_order_details', function ($join) {
                $join->on(
                    'sales_orders.id',
                    '=',
                    'sales_order_details.sales_order_id'
                )
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_products_units', 'm_products.id', '=', 'm_products_units.product_id')
            ->where('transaction_type_id', TransactionType::ON_ACCOUNT)
            ->unionAll($order_deposit)
            ->oldest('customer_id')
            ->oldest('order_type')
            ->oldest('order_date')
            ->oldest('order_number')
            ->oldest('sort')
            ->get();
    }
    // endregion static method
}
