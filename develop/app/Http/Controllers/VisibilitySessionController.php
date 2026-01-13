<?php

/**
 * VisibilitySession用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class VisibilitySessionController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setOrdersReceived(Request $request)
    {
        try {
            $data = [
                'current_url' => $request->has('current_url') ? $request->current_url : null,
                'order_date' => $request->has('order_date') ? $request->order_date : null,
                'customer_id' => $request->has('customer_id') ? $request->customer_id : null,
                'customer_delivery_id' => $request->has('customer_delivery_id') ? $request->customer_delivery_id : null,
                'branch_id' => $request->has('branch_id') ? $request->branch_id : null,
                'recipient_id' => $request->has('recipient_id') ? $request->recipient_id : null,
                'employee_id' => $request->has('employee_id') ? $request->employee_id : null,
                'detail.0.product_id' => $request->has('detail_0_product_id') ? $request->get('detail_0_product_id') : null,
                'detail.0.product_name' => $request->has('detail_0_product_name') ? $request->get('detail_0_product_name') : null,
                'detail.0.quantity' => $request->has('detail_0_quantity') ? $request->get('detail_0_quantity') : null,
                'detail.0.delivery_date' => $request->has('detail_0_delivery_date') ? $request->get('detail_0_delivery_date') : null,
                'detail.0.warehouse_id' => $request->has('detail_0_warehouse_id') ? $request->get('detail_0_warehouse_id') : null,
                'detail.0.note' => $request->has('detail_0_note') ? $request->get('detail_0_note') : null,
                'detail.0.sales_confirm' => $request->has('detail_0_sales_confirm') ? $request->get('detail_0_sales_confirm') : null,
                'detail.0.checked_sales_confirm' => $request->has('detail_0_checked_sales_confirm') ? $request->get('detail_0_checked_sales_confirm') : null,
                'detail.0.sort' => $request->has('detail_0_sort') ? $request->get('detail_0_sort') : null,
                'detail.1.product_id' => $request->has('detail_1_product_id') ? $request->get('detail_1_product_id') : null,
                'detail.1.product_name' => $request->has('detail_1_product_name') ? $request->get('detail_1_product_name') : null,
                'detail.1.quantity' => $request->has('detail_1_quantity') ? $request->get('detail_1_quantity') : null,
                'detail.1.delivery_date' => $request->has('detail_1_delivery_date') ? $request->get('detail_1_delivery_date') : null,
                'detail.1.warehouse_id' => $request->has('detail_1_warehouse_id') ? $request->get('detail_1_warehouse_id') : null,
                'detail.1.note' => $request->has('detail_1_note') ? $request->get('detail_1_note') : null,
                'detail.1.sales_confirm' => $request->has('detail_1_sales_confirm') ? $request->get('detail_1_sales_confirm') : null,
                'detail.1.checked_sales_confirm' => $request->has('detail_1_checked_sales_confirm') ? $request->get('detail_1_checked_sales_confirm') : null,
                'detail.1.sort' => $request->has('detail_1_sort') ? $request->get('detail_1_sort') : null,
                'detail.2.product_id' => $request->has('detail_2_product_id') ? $request->get('detail_2_product_id') : null,
                'detail.2.product_name' => $request->has('detail_2_product_name') ? $request->get('detail_2_product_name') : null,
                'detail.2.quantity' => $request->has('detail_2_quantity') ? $request->get('detail_2_quantity') : null,
                'detail.2.delivery_date' => $request->has('detail_2_delivery_date') ? $request->get('detail_2_delivery_date') : null,
                'detail.2.warehouse_id' => $request->has('detail_2_warehouse_id') ? $request->get('detail_2_warehouse_id') : null,
                'detail.2.note' => $request->has('detail_2_note') ? $request->get('detail_2_note') : null,
                'detail.2.sales_confirm' => $request->has('detail_2_sales_confirm') ? $request->get('detail_2_sales_confirm') : null,
                'detail.2.checked_sales_confirm' => $request->has('detail_2_checked_sales_confirm') ? $request->get('detail_2_checked_sales_confirm') : null,
                'detail.2.sort' => $request->has('detail_2_sort') ? $request->get('detail_2_sort') : null,
                'detail.3.product_id' => $request->has('detail_3_product_id') ? $request->get('detail_3_product_id') : null,
                'detail.3.product_name' => $request->has('detail_3_product_name') ? $request->get('detail_3_product_name') : null,
                'detail.3.quantity' => $request->has('detail_3_quantity') ? $request->get('detail_3_quantity') : null,
                'detail.3.delivery_date' => $request->has('detail_3_delivery_date') ? $request->get('detail_3_delivery_date') : null,
                'detail.3.warehouse_id' => $request->has('detail_3_warehouse_id') ? $request->get('detail_3_warehouse_id') : null,
                'detail.3.note' => $request->has('detail_3_note') ? $request->get('detail_3_note') : null,
                'detail.3.sales_confirm' => $request->has('detail_3_sales_confirm') ? $request->get('detail_3_sales_confirm') : null,
                'detail.3.checked_sales_confirm' => $request->has('detail_3_checked_sales_confirm') ? $request->get('detail_3_checked_sales_confirm') : null,
                'detail.3.sort' => $request->has('detail_3_sort') ? $request->get('detail_3_sort') : null,
                'detail.4.product_id' => $request->has('detail_4_product_id') ? $request->get('detail_4_product_id') : null,
                'detail.4.product_name' => $request->has('detail_4_product_name') ? $request->get('detail_4_product_name') : null,
                'detail.4.quantity' => $request->has('detail_4_quantity') ? $request->get('detail_4_quantity') : null,
                'detail.4.delivery_date' => $request->has('detail_4_delivery_date') ? $request->get('detail_4_delivery_date') : null,
                'detail.4.warehouse_id' => $request->has('detail_4_warehouse_id') ? $request->get('detail_4_warehouse_id') : null,
                'detail.4.note' => $request->has('detail_4_note') ? $request->get('detail_4_note') : null,
                'detail.4.sales_confirm' => $request->has('detail_4_sales_confirm') ? $request->get('detail_4_sales_confirm') : null,
                'detail.4.checked_sales_confirm' => $request->has('detail_4_checked_sales_confirm') ? $request->get('detail_4_checked_sales_confirm') : null,
                'detail.4.sort' => $request->has('detail_4_sort') ? $request->get('detail_4_sort') : null,
            ];

            Session::put('visibility_session.orders_received', $data);

        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => $e->getMessage(),
                ]
            );
        }

        return response()->json(
            [
                'message' => 'setOrdersReceived 正常終了',
            ]
        );
    }
}
