<?php

/**
 * 売掛台帳用モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Sale\Ledger;

use App\Consts\DB\Master\MasterProductsConst;
use App\Consts\DB\Sale\DepositOrderConst;
use App\Consts\DB\Sale\SalesOrderConst;
use App\Enums\OrderType;
use App\Enums\TransactionType;
use App\Models\Sale\DepositOrder;
use App\Models\Sale\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 売掛台帳用モデル
 */
class LedgerAccountReceivableBalance extends Model
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
        $target_customer_id = $search_condition['customer_id'] ?? null;   // 得意先ID
        $target_order_date = $search_condition['order_date'] ?? null;    // 伝票日付

        $order_number_maxlength = SalesOrderConst::ORDER_NUMBER_MAX_LENGTH;
        $deposit_number_maxlength = DepositOrderConst::ORDER_NUMBER_MAX_LENGTH;
        $product_code_maxlength = MasterProductsConst::CODE_MAX_LENGTH;

        // 入金伝票用カラム
        $arr_select_column = [
            /** 伝票ID */
            DB::raw('deposit_orders.id AS order_id'),
            /** 伝票日付 */
            DB::raw("date_format(deposit_orders.order_date, '%Y/%m/%d') AS order_date"),
            /** 伝票番号 */
            DB::raw("lpad(deposit_orders.order_number, {$deposit_number_maxlength}, '0') AS order_number"),
            /** 種別 */
            DB::raw(OrderType::DEPOSIT . ' AS order_kind'),
            /** 支所名 */
            DB::raw("'' AS branch_n"),
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
            /** 金額 */
            DB::raw("'' AS sub_total"),
            /** 消費税 */
            DB::raw('0 AS sub_total_tax'),
            /** 入金 */
            DB::raw('deposit_orders.deposit AS deposit_total'),
            /** 備考 */
            DB::raw('deposit_orders.note AS note'),
            /** 作成日時 */
            DB::raw('deposit_orders.created_at AS created_at'),
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
            /** 現金備考 */
            DB::raw('deposit_order_details.note_cash AS note_cash'),
            /** 小切手備考 */
            DB::raw('deposit_order_details.note_check AS note_check'),
            /** 振込備考 */
            DB::raw('deposit_order_details.note_transfer AS note_transfer'),
            /** 手形備考 */
            DB::raw('deposit_order_details.note_bill AS note_bill'),
            /** 相殺備考 */
            DB::raw('deposit_order_details.note_offset AS note_offset'),
            /** 値引備考 */
            DB::raw('deposit_order_details.note_discount AS note_discount'),
            /** 手数料備考 */
            DB::raw('deposit_order_details.note_fee AS note_fee'),
            /** その他備考*/
            DB::raw('deposit_order_details.note_other AS note_other'),
        ];

        $order_deposit = DepositOrder::select($arr_select_column)
            ->join('deposit_order_details', function ($join) {
                $join->on('deposit_orders.id', '=', 'deposit_order_details.deposit_order_id')
                    ->whereNull('deposit_order_details.deleted_at');
            })
            ->when(isset($target_customer_id), function ($query) use ($target_customer_id) {
                // 得意先IDで絞り込み
                return $query->customerId($target_customer_id);
            })
            ->when(isset($target_order_date), function ($query) use ($target_order_date) {
                // 伝票日付で絞り込み
                return $query->orderDate($target_order_date['start'] ?? null, $target_order_date['end'] ?? null);
            });

        // 売上伝票明細用カラム
        $arr_select_column = [
            /** 伝票ID */
            DB::raw('sales_orders.id AS order_id'),
            /** 伝票日付 */
            DB::raw("date_format(sales_orders.order_date, '%Y/%m/%d') AS order_date"),
            /** 伝票番号 */
            DB::raw("lpad(sales_orders.order_number, {$order_number_maxlength}, '0') AS order_number"),
            /** 種別 */
            DB::raw('cast(sales_orders.transaction_type_id AS UNSIGNED) AS order_kind'),
            /** 支所名 */
            DB::raw('m_branches.name AS branch_n'),
            /** 商品コード */
            DB::raw("lpad(m_products.code, {$product_code_maxlength}, '0') AS product_code"),
            /** 商品名 */
            DB::raw('sales_order_details.product_name'),
            /** 数量 */
            DB::raw('sales_order_details.quantity'),
            /** 数量小数桁数 */
            DB::raw('m_products.quantity_decimal_digit'),
            /** 単位 */
            DB::raw('sales_order_details.unit_name'),
            /** 単価 */
            DB::raw('sales_order_details.unit_price'),
            /** 単価小数桁数 */
            DB::raw('m_products.unit_price_decimal_digit'),
            /** 金額 */
            DB::raw('sales_order_details.sub_total AS sub_total'),
            /** 消費税 */
            DB::raw('sales_order_details.sub_total_tax AS sub_total_tax'),
            /** 入金 */
            DB::raw("'' AS deposit_total"),
            /** 備考 */
            DB::raw('sales_order_details.note AS note'),
            /** 作成日時 */
            DB::raw('sales_orders.created_at AS created_at'),
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
            /** 現金備考 */
            DB::raw("'' AS note_cash"),
            /** 小切手備考 */
            DB::raw("'' AS note_check"),
            /** 振込備考 */
            DB::raw("'' AS note_transfer"),
            /** 手形備考 */
            DB::raw("'' AS note_bill"),
            /** 相殺備考 */
            DB::raw("'' AS note_offset"),
            /** 値引備考 */
            DB::raw("'' AS note_discount"),
            /** 手数料備考 */
            DB::raw("'' AS note_fee"),
            /** その他備考*/
            DB::raw("'' AS note_other"),
        ];

        // 伝票データ
        return SalesOrder::query()
            ->select($arr_select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_products_units', 'm_products.id', '=', 'm_products_units.product_id')
            ->leftJoin('m_branches', 'sales_orders.branch_id', '=', 'm_branches.id')
            ->transactionTypeId(TransactionType::ON_ACCOUNT)     // 売掛のみ
            ->when(isset($target_customer_id), function ($query) use ($target_customer_id) {
                // 得意先IDで絞り込み
                return $query->customerId($target_customer_id);
            })
            ->when(isset($target_order_date), function ($query) use ($target_order_date) {
                // 伝票日付で絞り込み
                return $query->orderDate($target_order_date['start'] ?? null, $target_order_date['end'] ?? null);
            })
            ->unionAll($order_deposit)
            ->oldest('order_date')
            ->oldest('order_number')
            ->latest();
    }

    /**
     * 伝票データ取得
     *
     * @param array $search_condition 検索項目
     * @return Collection
     */
    public static function getOrder(array $search_condition): Collection
    {
        // 伝票データ
        return self::getData($search_condition)
            ->get();
    }

    /**
     * 伝票データ取得
     *
     * @param array $search_condition 検索項目
     * @return LengthAwarePaginator
     */
    public static function getOrderPaginate(array $search_condition): LengthAwarePaginator
    {
        // 伝票データ
        return self::getData($search_condition)
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * ■入金明細の内訳を配列で返す
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
