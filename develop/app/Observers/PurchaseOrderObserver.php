<?php

/**
 * 発注伝票オブザーバ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Observers;

use App\Models\Trading\PurchaseOrder;
use App\Models\Trading\PurchaseOrderStatusHistory;

/**
 * 発注伝票オブザーバ
 */
class PurchaseOrderObserver
{
    /**
     * Handle the app models trading purchase order "created" event.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return void
     */
    public function created(PurchaseOrder $purchaseOrder)
    {
        // 履歴登録
        PurchaseOrderStatusHistory::create(
            [
                /** 発注伝票ID */
                'purchase_order_id' => $purchaseOrder->id,
                /** 状態 */
                'order_status' => $purchaseOrder->order_status,
                /** 更新者 */
                'updated_id' => $purchaseOrder->updated_id,
            ]
        );
    }

    /**
     * Handle the app models trading purchase order "updated" event.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return void
     */
    public function updated(PurchaseOrder $purchaseOrder)
    {
        // ステータス更新時のみ
        if ($purchaseOrder->isDirty('order_status')) {
            // 履歴登録
            PurchaseOrderStatusHistory::create(
                [
                    /** 発注伝票ID */
                    'purchase_order_id' => $purchaseOrder->id,
                    /** 状態 */
                    'order_status' => $purchaseOrder->order_status,
                    /** 更新者 */
                    'updated_id' => $purchaseOrder->updated_id,
                ]
            );
        }
    }
}
