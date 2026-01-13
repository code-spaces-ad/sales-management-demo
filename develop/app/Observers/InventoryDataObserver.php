<?php

/**
 * 在庫オブザーバ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Observers;

use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryDataStatusHistory;

/**
 * 在庫オブザーバ
 */
class InventoryDataObserver
{
    /**
     * Handle the app models trading purchase order "created" event.
     *
     * @param InventoryData $inventoryData
     * @return void
     */
    public function created(InventoryData $inventoryData)
    {
        // 履歴登録
        InventoryDataStatusHistory::create(
            [
                /** ID */
                'inventory_data_id' => $inventoryData->id,
                /** 状態 */
                'inout_status' => $inventoryData->inout_status,
                /** 更新者 */
                'updated_id' => $inventoryData->updated_id,
            ]
        );
    }

    /**
     * Handle the app models trading purchase order "updated" event.
     *
     * @param InventoryData $inventoryData
     * @return void
     */
    public function updated(InventoryData $inventoryData)
    {
        // 履歴登録
        InventoryDataStatusHistory::create(
            [
                /** ID */
                'inventory_data_id' => $inventoryData->id,
                /** 状態 */
                'inout_status' => $inventoryData->inout_status,
                /** 更新者 */
                'updated_id' => $inventoryData->updated_id,
            ]
        );
    }
}
