<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Models\Master\MasterProduct;
use App\Models\Sale\SalesOrderDetail;
use Illuminate\Http\JsonResponse;

/**
 * 販売伝票用ヘルパークラス
 */
class SalesOrderHelper
{
    /**
     * 得意先・商品毎の単価履歴を返す
     *
     * @param int $customer_id
     * @param int $product_id
     * @param int $count
     * @param bool $distinct
     * @return JsonResponse
     */
    public static function getSalesUnitPriceHistory(int $customer_id, int $product_id, int $count, bool $distinct = false): JsonResponse
    {
        // 商品マスタの単価を取得
        $master_unit_price = MasterProduct::query()
            ->where('id', $product_id)
            ->value('unit_price');

        // 単価履歴取得
        $query = SalesOrderDetail::query()
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_details.sales_order_id')
            ->where('sales_orders.customer_id', $customer_id)
            ->where('sales_order_details.product_id', $product_id)
            ->where('sales_order_details.unit_price', '<>', $master_unit_price)
            ->orderByDesc('sales_orders.order_date')
            ->orderByDesc('sales_order_details.sales_order_id')
            ->select('sales_order_details.unit_price', 'sales_orders.order_date');

        $histories = $query->get();
        if ($distinct) {
            $histories = $histories->unique('unit_price');
        }

        return response()->json($histories->take($count)->values()->toArray());
    }
}
