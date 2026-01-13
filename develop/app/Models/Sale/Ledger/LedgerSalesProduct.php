<?php

/**
 * 商品別売上表用モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Sale\Ledger;

use App\Consts\DB\Master\MasterProductsConst;
use App\Models\Sale\SalesOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 商品別売上表用モデル
 */
class LedgerSalesProduct extends Model
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
        $target_product_id = $search_condition['product_id'] ?? null;   // 商品ID
        $target_order_date = $search_condition['order_date'] ?? null;   // 伝票日付

        $product_code_maxlength = MasterProductsConst::CODE_MAX_LENGTH;

        $arr_select_column_sales_total = [
            DB::raw('ROUND(SUM(sales_order_details.quantity * sales_order_details.unit_price)) as sum_sales_total'),
        ];

        // 売上金額合計（構成比用）
        $summary_sales_total = SalesOrder::query()
            ->select($arr_select_column_sales_total)
            ->join('sales_order_details', function ($join) use ($target_product_id) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at')
                    ->when(isset($target_product_id), function ($query) use ($target_product_id) {
                        // 商品IDで絞り込み
                        return $query->where('sales_order_details.product_id', $target_product_id);
                    });
            })
            ->when(isset($target_order_date), function ($query) use ($target_order_date) {
                // 伝票日付で絞り込み
                return $query->orderDate($target_order_date['start'] ?? null, $target_order_date['end'] ?? null);
            })
            ->value('sum_sales_total') ?? 0;

        $arr_select_column = [
            /** 商品コード */
            DB::raw("LPAD(m_products.code, {$product_code_maxlength}, '0') AS product_code"),
            /** 商品名 */
            DB::raw('sales_order_details.product_name'),
            /** 売上数量 */
            DB::raw('SUM(sales_order_details.quantity) AS quantity'),
            /** 数量小数桁数 */
            DB::raw('m_products.quantity_decimal_digit'),
            /** 単位 */
            DB::raw('sales_order_details.unit_name'),
            /** 売上金額 */
            DB::raw('ROUND(SUM(sales_order_details.quantity * sales_order_details.unit_price)) AS sales_total'),
            /** 粗利率(％) */
            DB::raw('100.0 AS gross_profit_margin'),
            /** 構成比(％) */
            DB::raw('ROUND(SUM(sales_order_details.quantity * sales_order_details.unit_price)/'
                . "{$summary_sales_total} * 100, 2) AS composition_ratio"),
            /** 順位 */
            DB::raw('0 AS "rank"'),   // ※クエリ実行後に差し替え
        ];

        // 伝票データ
        $order_details = SalesOrder::query()
            ->join('sales_order_details', function ($join) use ($target_product_id) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at')
                    ->when(isset($target_product_id), function ($query) use ($target_product_id) {
                        // 商品IDで絞り込み
                        return $query->where('sales_order_details.product_id', $target_product_id);
                    });
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_products_units', 'm_products.id', '=', 'm_products_units.product_id')
            ->when(isset($target_order_date), function ($query) use ($target_order_date) {
                // 伝票日付で絞り込み
                return $query->orderDate($target_order_date['start'] ?? null, $target_order_date['end'] ?? null);
            })
            ->groupBy('m_products.code')
            ->groupBy('m_products.quantity_decimal_digit')
            ->groupBy('sales_order_details.product_name')
            ->groupBy('sales_order_details.unit_name')
            ->latest('sales_total')    // 売上金額の降順
            ->get($arr_select_column);

        // 順位修正
        $rank = 1;
        foreach ($order_details as $key => $detail) {
            if ($key > 0) {
                // ひとつ前の売上金額を取得
                $before_sales_total = $order_details[$key - 1]->sales_total;
                if ($before_sales_total > $detail->sales_total) {
                    // ※ひとつ前と売上金額が同じ場合は、同順位とする。
                    ++$rank;
                }
            }

            // 順位差し替え
            $detail->rank = $rank;
        }

        return $order_details;
    }

    // endregion static method
}
