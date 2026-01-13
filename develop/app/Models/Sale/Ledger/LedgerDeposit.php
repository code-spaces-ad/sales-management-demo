<?php

/**
 * 入金台帳用モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Sale\Ledger;

use App\Consts\DB\Master\MasterCustomersConst;
use App\Models\Sale\DepositOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 入金台帳用モデル
 */
class LedgerDeposit extends Model
{
    // region static method

    /**
     * 伝票データ取得
     *
     * @param array $search_condition 検索項目
     * @return mixed
     */
    public static function getOrder(array $search_condition)
    {
        $target_order_date = $search_condition['order_date'] ?? null;   // 伝票日付

        $customer_code_maxlength = MasterCustomersConst::CODE_MAX_LENGTH;

        $arr_select_column = [
            /** 得意先コード */
            DB::raw("lpad(m_customers.code, {$customer_code_maxlength}, '0') AS customer_code"),
            /** 得意先名 */
            DB::raw('m_customers.name AS c_name'),
            /** 現金 */
            DB::raw('FORMAT(SUM(deposit_order_details.amount_cash), 0) AS amount_cash'),
            /** 小切手 */
            DB::raw('FORMAT(SUM(deposit_order_details.amount_check), 0) AS amount_check'),
            /** 振込 */
            DB::raw('FORMAT(SUM(deposit_order_details.amount_transfer), 0) AS amount_transfer'),
            /** 手形 */
            DB::raw('FORMAT(SUM(deposit_order_details.amount_bill), 0) AS amount_bill'),
            /** 相殺 */
            DB::raw('FORMAT(SUM(deposit_order_details.amount_offset), 0) AS amount_offset'),
            /** 値引 */
            DB::raw('FORMAT(SUM(deposit_order_details.amount_discount), 0) AS amount_discount'),
            /** 手数料 */
            DB::raw('FORMAT(SUM(deposit_order_details.amount_fee), 0) AS amount_fee'),
            /** その他 */
            DB::raw('FORMAT(SUM(deposit_order_details.amount_other), 0) AS amount_other'),
            /** 合計 */
            DB::raw('FORMAT(SUM(deposit_orders.deposit), 0) AS total_deposit'),
        ];

        // 伝票データ
        return DepositOrder::Join('deposit_order_details', 'deposit_orders.id', '=', 'deposit_order_details.deposit_order_id')
            ->leftJoin('m_customers', 'deposit_orders.customer_id', '=', 'm_customers.id')
            ->when(isset($target_order_date), function ($query) use ($target_order_date) {
                // 伝票日付で絞り込み
                return $query->orderDate($target_order_date['start'] ?? null, $target_order_date['end'] ?? null);
            })
            ->groupBy('deposit_orders.customer_id')
            ->groupBy('m_customers.code')
            ->groupBy('m_customers.name')
            ->oldest('m_customers.code')   // 得意先コード昇順
            ->get($arr_select_column);
    }

    // endregion static method
}
