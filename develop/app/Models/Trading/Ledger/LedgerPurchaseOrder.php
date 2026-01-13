<?php

/**
 * 仕入台帳用モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Trading\Ledger;

use App\Consts\DB\Trading\PurchaseOrderConst;
use App\Models\Trading\Payment;
use App\Models\Trading\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use OrderType;

/**
 * 仕入台帳用モデル
 */
class LedgerPurchaseOrder extends Model
{
    // region static method

    /**
     * 伝票データ取得
     *
     * @param array $search_condition 検索項目
     * @return Builder
     */
    public static function getData(array $search_condition): Builder
    {
        $target_supplier_id = $search_condition['supplier_id'] ?? null;   // 仕入先ID
        $target_order_date = $search_condition['order_date'] ?? null;    // 発注日付;

        $order_number_maxlength = PurchaseOrderConst::ORDER_NUMBER_MAX_LENGTH;

        // 仕入伝票用カラム
        $arr_select_column = [
            /** 伝票日付 */
            DB::raw("date_format(purchase_orders.order_date, '%Y/%m/%d') AS order_date"),
            /** 伝票番号 */
            DB::raw("lpad(purchase_orders.order_number, $order_number_maxlength, '0') AS order_number"),
            /** 種別 */
            DB::raw('cast(' . OrderType::PURCHASE . ' AS UNSIGNED) AS order_kind'),
            /** 商品コード */
            DB::raw('m_products.code AS product_code'),
            /** 商品名 */
            DB::raw('purchase_order_details.product_name'),
            /** 数量 */
            DB::raw('cast(purchase_order_details.quantity AS SIGNED) AS quantity'),
            /** 単位 */
            DB::raw('purchase_order_details.unit_name'),
            /** 単価 */
            DB::raw('cast(purchase_order_details.unit_price AS SIGNED) AS unit_price'),
            /** 金額 */
            DB::raw('cast(purchase_order_details.sub_total AS SIGNED) AS purchase_total'),
            /** 消費税 */
            DB::raw('cast(purchase_order_details.sub_total_tax AS SIGNED) AS sub_total_tax'),
            /** ソート */
            DB::raw('purchase_order_details.sort AS sort'),
            DB::raw("'' AS amount_cash"),
            DB::raw("'' AS amount_check"),
            DB::raw("'' AS amount_transfer"),
            DB::raw("'' AS amount_bill"),
            DB::raw("'' AS amount_offset"),
            DB::raw("'' AS amount_discount"),
            DB::raw("'' AS amount_fee"),
            DB::raw("'' AS amount_other"),
            /** 支払備考 */
            DB::raw("'' AS note_cash"),
            DB::raw("'' AS note_check"),
            DB::raw("'' AS note_transfer"),
            DB::raw("'' AS note_bill"),
            DB::raw("'' AS note_offset"),
            DB::raw("'' AS note_discount"),
            DB::raw("'' AS note_fee"),
            DB::raw("'' AS note_other"),
            /** 支払 */
            DB::raw("cast('' AS SIGNED) AS payment"),
            /** 備考 */
            DB::raw('purchase_order_details.note AS note'),
            /** 作成日時 */
            DB::raw('purchase_orders.created_at AS created_at'),
            /** 削除日時 */
            DB::raw('purchase_orders.deleted_at AS deleted_at'),
        ];

        $purchase_orders = PurchaseOrder::query()
            ->select($arr_select_column)
            ->leftjoin('purchase_order_details', function ($join) {
                $join->on('purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
                    ->whereNull('purchase_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'purchase_order_details.product_id', '=', 'm_products.id')
            ->when(isset($target_supplier_id), function ($query) use ($target_supplier_id) {
                // 仕入先IDで絞り込み
                return $query->supplierId($target_supplier_id);
            })
            ->when(isset($target_order_date), function ($query) use ($target_order_date) {
                // 伝票日付で絞り込み
                return $query->orderDate($target_order_date['start'] ?? null, $target_order_date['end'] ?? null);
            });

        // 支払伝票用カラム
        $arr_select_column = [
            /** 伝票日付 */
            DB::raw("date_format(payments.order_date, '%Y/%m/%d') AS order_date"),
            /** 伝票番号 */
            DB::raw("lpad(payments.order_number, $order_number_maxlength, '0') AS order_number"),
            /** 種別 */
            DB::raw('cast(' . OrderType::PAYMENT . ' AS UNSIGNED) AS order_kind'),
            /** 商品コード */
            DB::raw("'' AS product_code"),
            /** 商品名 */
            DB::raw("'' AS product_name"),
            /** 数量 */
            DB::raw("cast('' AS SIGNED) AS quantity"),
            /** 単位 */
            DB::raw("'' AS unit_name"),
            /** 単価 */
            DB::raw("cast('' AS SIGNED) AS unit_price"),
            /** 金額 */
            DB::raw("cast('' AS SIGNED) AS purchase_total"),
            /** 消費税 */
            DB::raw("cast('' AS SIGNED) AS sub_total_tax"),
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
            /** 支払備考 */
            DB::raw('payment_details.note_cash AS note_cash'),
            DB::raw('payment_details.note_check AS note_check'),
            DB::raw('payment_details.note_transfer AS note_transfer'),
            DB::raw('payment_details.note_bill AS note_bill'),
            DB::raw('payment_details.note_offset AS note_offset'),
            DB::raw('payment_details.note_discount AS note_discount'),
            DB::raw('payment_details.note_fee AS note_fee'),
            DB::raw('payment_details.note_other AS note_other'),
            /** 支払 */
            DB::raw('cast(payments.payment AS SIGNED) AS payment'),
            /** 備考 */
            DB::raw('payments.note AS note'),
            /** 作成日時 */
            DB::raw('payments.created_at AS created_at'),
            /** 削除日時 */
            DB::raw('payments.deleted_at AS deleted_at'),
        ];

        // 伝票データ（現売、掛売）
        return Payment::query()
            ->select($arr_select_column)
            ->leftjoin('payment_details', function ($join) {
                $join->on('payments.id', '=', 'payment_details.payment_id')
                    ->whereNull('payment_details.deleted_at');
            })
            ->when(isset($target_supplier_id), function ($query) use ($target_supplier_id) {
                // 仕入先IDで絞り込み
                return $query->supplierId($target_supplier_id);
            })
            ->when(isset($target_order_date), function ($query) use ($target_order_date) {
                // 伝票日付で絞り込み
                return $query->orderDate($target_order_date['start'] ?? null, $target_order_date['end'] ?? null);
            })
            ->unionAll($purchase_orders);
    }

    /**
     * 伝票データ取得
     *
     * @param array $search_condition 検索項目
     * @param string $sort
     * @return Collection
     */
    public static function getPurchaseOrder(array $search_condition, string $sort = 'desc'): Collection
    {
        // 仕入伝票データ
        return self::getData($search_condition)
            ->when($sort === 'desc', function ($query) {
                // 作成日時の降順
                return $query
                    ->latest('order_date')
                    ->latest('sort')
                    ->oldest();
            })
            ->when($sort === 'asc', function ($query) {
                // 作成日時の昇順
                return $query
                    ->oldest('order_date')
                    ->oldest('sort')
                    ->latest();
            })
            ->get();
    }

    /**
     * 伝票データ取得
     *
     * @param array $search_condition
     * @param string $sort
     * @return LengthAwarePaginator
     */
    public static function getPurchaseOrderPaginate(array $search_condition, string $sort = 'desc'): LengthAwarePaginator
    {
        // 仕入伝票データ
        return self::getData($search_condition)
            ->when($sort === 'desc', function ($query) {
                // 作成日時の降順
                return $query
                    ->latest('order_date')
                    ->latest('sort')
                    ->oldest();
            })
            ->when($sort === 'asc', function ($query) {
                // 作成日時の昇順
                return $query
                    ->oldest('order_date')
                    ->oldest('sort')
                    ->latest();
            })
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * ■支払明細の内訳を配列で返す
     *
     * @param $order_detail
     * @return array[]
     */
    public static function getDepositPaymentDetail1($order_detail): array
    {
        return
            [
                ['amount' => $order_detail->amount_cash,
                    'note' => $order_detail->note_cash, 'number' => null, 'date' => null],
                ['amount' => $order_detail->amount_check,
                    'note' => $order_detail->note_check, 'number' => null, 'date' => null],
                ['amount' => $order_detail->amount_transfer,
                    'note' => $order_detail->note_transfer, 'number' => null, 'date' => null],
                ['amount' => $order_detail->amount_bill,
                    'note' => $order_detail->note_bill, 'number' => null, 'date' => null],
                ['amount' => $order_detail->amount_offset,
                    'note' => $order_detail->note_offset, 'number' => null, 'date' => null],
                ['amount' => $order_detail->amount_discount,
                    'note' => $order_detail->note_discount, 'number' => null, 'date' => null],
                ['amount' => $order_detail->amount_fee,
                    'note' => $order_detail->note_fee, 'number' => null, 'date' => null],
                ['amount' => $order_detail->amount_other,
                    'note' => $order_detail->note_other, 'number' => null, 'date' => null],
            ];
    }
    // endregion static method
}
