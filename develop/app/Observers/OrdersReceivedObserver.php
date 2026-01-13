<?php

/**
 * 発注伝票オブザーバ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Observers;

use App\Models\Receive\OrdersReceived;
use App\Models\Receive\OrdersReceivedStatusHistory;

/**
 * 発注伝票オブザーバ
 */
class OrdersReceivedObserver
{
    /**
     * Handle the app models trading purchase order "created" event.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return void
     */
    public function created(OrdersReceived $ordersreceived)
    {
        // 履歴登録
        OrdersReceivedStatusHistory::create(
            [
                /** 発注伝票ID */
                'orders_received_id' => $ordersreceived->id,
                /** 状態 */
                'order_status' => $ordersreceived->order_status,
                /** 更新者 */
                'updated_id' => $ordersreceived->updated_id,
            ]
        );
    }

    /**
     * Handle the app models trading purchase order "updated" event.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return void
     */
    public function updated(OrdersReceived $ordersreceived)
    {
        // ステータス更新時のみ
        if ($ordersreceived->isDirty('order_status')) {
            // 履歴登録
            OrdersReceivedStatusHistory::create(
                [
                    /** 発注伝票ID */
                    'orders_received_id' => $ordersreceived->id,
                    /** 状態 */
                    'order_status' => $ordersreceived->order_status,
                    /** 更新者 */
                    'updated_id' => $ordersreceived->updated_id,
                ]
            );
        }
    }
}
