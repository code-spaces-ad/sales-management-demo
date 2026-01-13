<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Database\Factories\Inventory;

use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryStockDataFactory extends Factory
{
    protected $model = InventoryStockData::class;

    public function definition()
    {
        return [
            // 倉庫ID
            'warehouse_id' => MasterOfficeFacility::inRandomOrder()->first()->id,
            // 商品ID
            'product_id' => MasterProduct::inRandomOrder()->first()->id,
            // 現在庫数
            'inventory_stocks' => '100.0000',
            // 仕入れ金額合計
            'purchase_total_price' => null,
        ];
    }
}
