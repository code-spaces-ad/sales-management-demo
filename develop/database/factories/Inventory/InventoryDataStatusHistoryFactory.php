<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Database\Factories\Inventory;

use App\Models\Inventory\InventoryDataStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryDataStatusHistoryFactory extends Factory
{
    protected $model = InventoryDataStatusHistory::class;

    public function definition()
    {
        // 外から必ず渡すようにする
        return [
            // 在庫データID
            'inventory_data_id' => null,
            // 状態
            'inout_status' => 1,
            // 更新者ID
            'updated_id' => 1,
        ];
    }
}
