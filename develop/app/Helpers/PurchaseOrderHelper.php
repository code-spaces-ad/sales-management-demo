<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Models\Master\MasterProduct;
use App\Models\Trading\PurchaseOrderDetail;
use Illuminate\Http\JsonResponse;

/**
 * 仕入伝票用ヘルパークラス
 */
class PurchaseOrderHelper
{
    /**
     * 仕入先・商品毎の単価履歴を返す
     *
     * @param int $supplier_id
     * @param int $product_id
     * @param int $count
     * @param bool $distinct
     * @return JsonResponse
     */
    public static function getPurchaseUnitPriceHistory(int $supplier_id, int $product_id, int $count, bool $distinct = false): JsonResponse
    {

        // 商品マスタの仕入単価は除く
        $master_unit_price = MasterProduct::query()
            ->where('id', $product_id)
            ->value('purchase_unit_price');

        // 単価履歴取得
        $query = PurchaseOrderDetail::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
            ->where('purchase_orders.supplier_id', $supplier_id)
            ->where('purchase_order_details.product_id', $product_id)
            ->where('purchase_order_details.unit_price', '<>', $master_unit_price)
            ->orderByDesc('purchase_orders.order_date')
            ->orderByDesc('purchase_order_details.purchase_order_id')
            ->select('purchase_order_details.unit_price', 'purchase_orders.order_date');

        $histories = $query->get();
        if ($distinct) {
            $histories = $histories->unique('unit_price');
        }

        return response()->json($histories->take($count)->values()->toArray());
    }
}
