<?php

/**
 * 仕入締伝票明細モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\PurchaseInvoice;

use App\Consts\DB\Master\MasterProductsConst;
use App\Consts\DB\Trading\PaymentConst;
use App\Consts\DB\Trading\PurchaseOrderConst;
use App\Enums\OrderType;
use App\Models\Trading\Payment;
use App\Models\Trading\PurchaseOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\CashFlow\Constant\Periodic\Payments;

/**
 * 仕入締伝票明細モデル
 */
class PurchaseClosingDetail extends Model
{
    // region static method

    /**
     * 伝票データ取得
     *
     * @param array $purchase_order_ids
     * @param array $payment_ids
     * @return Collection
     */
    public static function getOrder(array $purchase_order_ids, array $payment_ids): Collection
    {
        $order_number_maxlength = PurchaseOrderConst::ORDER_NUMBER_MAX_LENGTH;
        $payment_number_maxlength = PaymentConst::ORDER_NUMBER_MAX_LENGTH;
        $product_code_maxlength = MasterProductsConst::CODE_MAX_LENGTH;

        // 支払伝票用カラム
        $arr_select_column_payment = [
            /** 伝票日付 */
            'payments.order_date',
            /** 伝票ID */
            DB::raw('payments.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(payments.order_number, {$payment_number_maxlength}, '0') AS order_number"),
            /** 伝票種別 */
            DB::raw(OrderType::PAYMENT . ' AS order_type'),
            /** 仕入先名 */
            'm_suppliers.name AS name',
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
            /** 仕入 */
            DB::raw("'' AS sub_total"),
            /** 支払 */
            DB::raw('payments.payment AS payment_total'),
            /** 備考 */
            DB::raw('payments.note AS detail_note'),
            /** ソート */
            DB::raw("'' AS sort"),
            DB::raw('payment_details.amount_cash AS amount_cash'),
            DB::raw('payment_details.amount_check AS amount_check'),
            DB::raw('payment_details.amount_transfer AS amount_transfer'),
            DB::raw('payment_details.amount_bill AS amount_bill'),
            DB::raw('payment_details.amount_offset AS amount_offset'),
            DB::raw('payment_details.amount_discount AS amount_discount'),
            DB::raw('payment_details.amount_fee AS amount_fee'),
            DB::raw('payment_details.amount_other AS amount_other'),
        ];

        $order_payment = Payment::select($arr_select_column_payment)
            ->join('payment_details', function ($join) {
                $join->on(
                    'payments.id',
                    '=',
                    'payment_details.payment_id'
                )
                    ->whereNull('payment_details.deleted_at');
            })
            ->leftJoin('m_suppliers', 'payments.supplier_id', '=', 'm_suppliers.id')
            ->whereIn('payments.id', $payment_ids);

        // 仕入伝票明細用カラム
        $arr_select_column_purchase_closing_detail = [
            /** 伝票日付 */
            'purchase_orders.order_date',
            /** 伝票ID */
            DB::raw('purchase_orders.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(purchase_orders.order_number, {$order_number_maxlength}, '0') AS order_number"),
            /** 伝票種別 */
            DB::raw(OrderType::DEPOSIT . ' AS order_type'),
            /** 仕入先名 */
            'm_suppliers.name AS name',
            /** 商品コード */
            DB::raw("LPAD(m_products.code, {$product_code_maxlength}, '0') AS product_code"),
            /** 商品名 */
            'purchase_order_details.product_name',
            /** 数量 */
            'purchase_order_details.quantity',
            /** 数量小数桁数 */
            'm_products.quantity_decimal_digit',
            /** 単位 */
            'purchase_order_details.unit_name',
            /** 単価 */
            'purchase_order_details.unit_price',
            /** 単価小数桁数 */
            'm_products.unit_price_decimal_digit',
            /** 仕入 */
            DB::raw('(purchase_order_details.quantity * purchase_order_details.unit_price) AS sub_total'),
            /** 支払 */
            DB::raw("'' AS payment_total"),
            /** 備考 */
            DB::raw('purchase_order_details.note AS detail_note'),
            /** ソート */
            DB::raw('purchase_order_details.sort'),
            DB::raw("'' AS amount_cash"),
            DB::raw("'' AS amount_check"),
            DB::raw("'' AS amount_transfer"),
            DB::raw("'' AS amount_bill"),
            DB::raw("'' AS amount_offset"),
            DB::raw("'' AS amount_discount"),
            DB::raw("'' AS amount_fee"),
            DB::raw("'' AS amount_other"),
        ];

        // 伝票データ
        return PurchaseOrder::select($arr_select_column_purchase_closing_detail)
            ->join('purchase_order_details', function ($join) {
                $join->on(
                    'purchase_orders.id',
                    '=',
                    'purchase_order_details.purchase_order_id'
                )
                    ->whereNull('purchase_order_details.deleted_at');
            })
            ->whereIn('purchase_orders.id', $purchase_order_ids)
            ->leftJoin('m_products', 'purchase_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_products_units', 'm_products.id', '=', 'm_products_units.product_id')
            ->leftJoin('m_suppliers', 'purchase_orders.supplier_id', '=', 'm_suppliers.id')
            ->unionAll($order_payment)
            ->oldest('order_number')
            ->oldest('order_date')
            ->oldest('sort')
            ->get();
    }

    /**
     * 伝票データ取得
     *
     * @param int $purchase_closing_id 仕入締データID
     * @return Collection
     */
    public static function getOrderDetail(int $purchase_closing_id): Collection
    {
        $order_number_maxlength = PurchaseOrderConst::ORDER_NUMBER_MAX_LENGTH;
        $payment_number_maxlength = PaymentConst::ORDER_NUMBER_MAX_LENGTH;
        $product_code_maxlength = MasterProductsConst::CODE_MAX_LENGTH;

        // 支払伝票用カラム
        $arr_select_column_payment_detail = [
            /** 仕入先ID */
            'payments.supplier_id',
            /** 仕入先名 */
            DB::raw('m_suppliers.name AS supplier_name'),
            /** 伝票日付 */
            'payments.order_date',
            /** 伝票ID */
            DB::raw('payments.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(payments.order_number, {$payment_number_maxlength}, '0') AS order_number"),
            /** 伝票種別 */
            DB::raw(OrderType::DEPOSIT . ' AS order_type'),
            /** 商品コード */
            DB::raw("'' AS product_code"),
            /** 商品名 */
            DB::raw("'' AS product_name"),
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
            /** 仕入 */
            DB::raw("'' AS sub_total"),
            /** 支払 */
            DB::raw('payments.payment AS payment_total'),
            /** 備考 */
            DB::raw('payments.note AS detail_note'),
            /** 金額_現金 */
            DB::raw('payment_details.amount_cash AS amount_cash'),
            /** 備考_現金 */
            DB::raw('payment_details.note_cash AS note_cash'),
            /** 金額_小切手 */
            DB::raw('payment_details.amount_check AS amount_check'),
            /** 備考_小切手 */
            DB::raw('payment_details.note_check AS note_check'),
            /** ソート */
            DB::raw('0 AS sort'),
            /** 金額_振込 */
            DB::raw('payment_details.amount_transfer AS amount_transfer'),
            /** 備考_振込 */
            DB::raw('payment_details.note_transfer AS note_transfer'),
            /** 金額_手形 */
            DB::raw('payment_details.amount_bill AS amount_bill'),
            /** 備考_手形 */
            DB::raw('payment_details.note_bill AS note_bill'),
            /** 金額_相殺 */
            DB::raw('payment_details.amount_offset AS amount_offset'),
            /** 備考_相殺 */
            DB::raw('payment_details.note_offset AS note_offset'),
            /** 金額_値引 */
            DB::raw('payment_details.amount_discount AS amount_discount'),
            /** 備考_値引 */
            DB::raw('payment_details.note_discount AS note_discount'),
            /** 金額_手数料 */
            DB::raw('payment_details.amount_fee AS amount_fee'),
            /** 備考_手数料 */
            DB::raw('payment_details.note_fee AS note_fee'),
            /** 金額_その他 */
            DB::raw('payment_details.amount_other AS amount_other'),
            /** 備考_その他 */
            DB::raw('payment_details.note_other AS note_other'),
            /** 手形_手形期日 */
            DB::raw('payment_bill.bill_date AS bill_date'),
            /** 手形_手形番号 */
            DB::raw('payment_bill.bill_number AS bill_number'),
        ];

        $order_payment = Payment::select($arr_select_column_payment_detail)
            ->join('purchase_closing_payment', function ($join) {
                $join->on(
                    'payments.id',
                    '=',
                    'purchase_closing_payment.payment_id'
                )
                    ->whereNull('payments.deleted_at');
            })
            ->where('purchase_closing_payment.charge_data_id', $purchase_closing_id)
            ->Join('m_suppliers', 'payments.supplert_id', '=', 'm_suppliers.id')
            ->join('payment_details', function ($join) {
                $join->on(
                    'payments.id',
                    '=',
                    'payment_details.payment_id'
                )
                    ->whereNull('payment_details.deleted_at');
            })
            ->leftjoin('payment_bill', function ($join) {
                $join->on(
                    'payments.id',
                    '=',
                    'payment_bill.dpayment_id'
                );
            });

        // 仕入伝票明細用カラム
        $arr_select_column_purchase_order_detail = [
            /** 仕入先ID */
            'payments.supplier_id',
            /** 仕入先名 */
            DB::raw('m_suppliers.name AS supplier_name'),
            /** 伝票日付 */
            'payments.order_date',
            /** 伝票ID */
            DB::raw('payments.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(payments.order_number, {$order_number_maxlength}, '0') AS order_number"),
            /** 伝票種別 */
            DB::raw(OrderType::ACCOUNTS_RECEIVABLE . ' AS order_type'),
            /** 商品コード */
            DB::raw("LPAD(m_products.code, {$product_code_maxlength}, '0') AS product_code"),
            /** 商品名 */
            'payment_details.product_name',
            /** 税タイプ */
            'payment_details.tax_type_id',
            /** 税率 */
            'payment_details.consumption_tax_rate',
            /** 税端数処理方法 */
            'payment_details.rounding_method_id',
            /** 数量 */
            'payment_details.quantity',
            /** 数量小数桁数 */
            'm_products.quantity_decimal_digit',
            /** 単位 */
            'payment_details.unit_name',
            /** 単価 */
            'payment_details.unit_price',
            /** 単価小数桁数 */
            'm_products.unit_price_decimal_digit',
            /** 仕入 */
            DB::raw('(payment_details.quantity * payment_details.unit_price) AS sub_total'),
            /** 支払 */
            DB::raw("'' AS payment_total"),
            /** 備考 */
            DB::raw('payment_details.note AS detail_note'),
            /** ソート */
            DB::raw('payment_details.sort AS sort'),
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
        return PurchaseClosing::select($arr_select_column_purchase_order_detail)
            ->join('purchase_closing_purchase_order', function ($join) {
                $join->on(
                    'purchase_orders.id',
                    '=',
                    'purchase_closing_purchase_order.purchase_order_id'
                )
                    ->whereNull('payments.deleted_at');
            })
            ->where('purchase_closing_purchase_order.charge_data_id', $purchase_closing_id)
            ->Join('m_suppliers', 'purchase_orders.supplier_id', '=', 'm_suppliers.id')
            ->join('purchase_order_details', function ($join) {
                $join->on(
                    'purchase_orders.id',
                    '=',
                    'purchase_order_details.purchase_order_id'
                )
                    ->whereNull('purchase_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'purchase_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_products_units', 'm_products.id', '=', 'm_products_units.product_id')
            // ->leftJoin('m_branches', 'purchase_orders.branch_id', '=', 'm_branches.id')
            ->union($order_payment)
            ->oldest('supplier_id')
            ->orderBy('order_type', 'desc')
            ->oldest('order_date')
            ->oldest('order_number')
            ->oldest('sort')
            ->get();
    }

    /**
     * 仕入先別伝票データ明細取得
     *
     * @param int $purchase_closing_id 仕入締データID
     * @param int $billing_supplier_id 仕入締先ID
     * @return Collection
     */
    public static function getOrderBillingSupplier(int $purchase_closing_id, int $billing_supplier_id): Collection
    {

        // 支払伝票用カラム
        $arr_select_column_payment_order_detail = [
            /** 仕入先ID */
            DB::raw('payments.supplier_id AS supplier_id'),
        ];

        $order_payment = Payments::select($arr_select_column_payment_order_detail)
            ->join('purchase_closing_payment', function ($join) {
                $join->on(
                    'payments.id',
                    '=',
                    'purchase_closing_payment.payment_id'
                )
                    ->whereNull('payments.deleted_at');
            })
            ->where('purchase_closing_payment.purchase_closing_id', $purchase_closing_id)
            ->where('payments.supplier_id', '<>', $billing_supplier_id);

        // 仕入伝票明細用カラム
        $arr_select_column_purchase_order_detail = [
            /** 仕入先ID */
            DB::raw('payments.supplier_id AS supplier_id'),
        ];

        // 伝票データ
        return PurchaseOrder::select($arr_select_column_purchase_order_detail)
            ->join('purchase_closing_purchase_order', function ($join) {
                $join->on(
                    'purchase_orders.id',
                    '=',
                    'purchase_closing_purchase_order.purchase_order_id'
                )
                    ->whereNull('purchase_orders.deleted_at');
            })
            ->where('purchase_closing_purchase_order.purchase_closing_id', $purchase_closing_id)
            ->where('payments.supplier_id', '<>', $billing_supplier_id)
            ->union($order_payment)
            ->oldest('supplier_id')
            ->get();
    }

    /**
     * 仕入先別伝票データ明細取得
     *
     * @param int $purchase_closing_id 仕入締データID
     * @return Collection
     */
    public static function getOrderBillingSupplierDetail(int $purchase_closing_id): Collection
    {
        $order_number_maxlength = PurchaseOrderConst::ORDER_NUMBER_MAX_LENGTH;
        $payment_number_maxlength = PaymentConst::ORDER_NUMBER_MAX_LENGTH;
        $product_code_maxlength = MasterProductsConst::CODE_MAX_LENGTH;

        // 支払伝票用カラム
        $arr_select_column_payment_order_detail = [
            /** 仕入先ID */
            'payments.supplier_id',
            /** 仕入先名 */
            'm_suppliers.name',
            /** 伝票日付 */
            'payments.order_date',
            /** 伝票ID */
            DB::raw('payments.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(payments.order_number, {$payment_number_maxlength}, '0') AS order_number"),
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
            /** 仕入 */
            DB::raw("'' AS sub_total"),
            /** 支払 */
            DB::raw('payments.payment AS payment_total'),
            /** 備考 */
            DB::raw('payments.note AS detail_note'),
            /** ソート */
            DB::raw('0 AS sort'),
        ];

        $order_payment = Payment::select($arr_select_column_payment_order_detail)
            ->join('purchase_closing_payment', function ($join) {
                $join->on(
                    'payments.id',
                    '=',
                    'purchase_closing_payment.payment_id'
                )
                    ->whereNull('payments.deleted_at');
            })
            ->join('m_suppliers', 'payments.supplier_id', '=', 'm_suppliers.id')
            ->where('purchase_closing_payment.charge_data_id', $purchase_closing_id)
            ->join('payment_details', function ($join) {
                $join->on(
                    'payments.id',
                    '=',
                    'payment_details.payment_id'
                )
                    ->whereNull('payment_details.deleted_at');

            });

        // 仕入伝票明細用カラム
        $arr_select_column_purchase_order_detail = [
            /** 仕入先ID */
            'purchase_orders.supplier_id',
            /** 仕入先名 */
            'm_suppliers.name',
            /** 伝票日付 */
            'purchase_orders.order_date',
            /** 伝票ID */
            DB::raw('purchase_orders.id AS order_id'),
            /** 伝票番号 */
            DB::raw("LPAD(purchase_orders.order_number, {$order_number_maxlength}, '0') AS order_number"),
            /** 伝票種別 */
            DB::raw(OrderType::ACCOUNTS_RECEIVABLE . ' AS order_type'),
            /** 商品コード */
            DB::raw("LPAD(m_products.code, {$product_code_maxlength}, '0') AS product_code"),
            /** 商品名 */
            'purchase_order_details.product_name',
            /** 税タイプ */
            'purchase_order_details.tax_type_id',
            /** 税率 */
            'purchase_order_details.consumption_tax_rate',
            /** 税端数処理方法 */
            'purchase_order_details.rounding_method_id',
            /** 数量 */
            'purchase_order_details.quantity',
            /** 数量小数桁数 */
            'm_products.quantity_decimal_digit',
            /** 単位 */
            'purchase_order_details.unit_name',
            /** 単価 */
            'purchase_order_details.unit_price',
            /** 単価小数桁数 */
            'm_products.unit_price_decimal_digit',
            /** 仕入 */
            DB::raw('purchase_order_details.sub_total AS sub_total'),
            /** 支払 */
            DB::raw("'' AS payment_total"),
            /** 備考 */
            DB::raw('purchase_order_details.note AS detail_note'),
            /** ソート */
            DB::raw('purchase_order_details.sort AS sort'),
        ];

        // 伝票データ
        return PurchaseOrder::select($arr_select_column_purchase_order_detail)
            ->join('purchase_closing_purchase_order', function ($join) {
                $join->on(
                    'purchase_orders.id',
                    '=',
                    'purchase_closing_purchase_order.purchase_order_id'
                )
                    ->whereNull('purchase_orders.deleted_at');
            })
            ->where('purchase_closing_purchase_order.charge_data_id', $purchase_closing_id)
            ->join('m_suppliers', 'purchase_orders.supplier_id', '=', 'm_suppliers.id')
            ->join('purchase_order_details', function ($join) {
                $join->on(
                    'purchase_orders.id',
                    '=',
                    'purchase_order_details.purchase_order_id'
                )
                    ->whereNull('purchase_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'purchase_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_products_units', 'm_products.id', '=', 'm_products_units.product_id')
            ->union($order_payment)
            ->oldest('supplier_id')
            ->oldest('order_type')
            ->oldest('order_date')
            ->oldest('order_number')
            ->oldest('sort')
            ->get();
    }
    // endregion static method
}
