<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Database\Factories\Inventory;

use App\Models\Inventory\InventoryDataClosing;
use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryDataClosingFactory extends Factory
{
    protected $model = InventoryDataClosing::class;

    public function definition()
    {
        return [
            // 倉庫ID
            'warehouse_id' => MasterOfficeFacility::inRandomOrder()->first()->id,
            // 商品ID
            'product_id' => MasterProduct::inRandomOrder()->first()->id,
            // 締年月
            'closing_ym' => Carbon::today()->startOfMonth()->subMonths(4)->format('Ym'),
            // 締在庫数
            'closing_stocks' => '50.0000',
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (InventoryDataClosing $inventoryDataClosing) {
            $faker = $this->faker;

            InventoryStockData::factory()->create([
                // 倉庫ID
                'warehouse_id' => $inventoryDataClosing->warehouse_id,
                // 商品ID
                'product_id' => $inventoryDataClosing->product_id,
                // 現在庫数
                'inventory_stocks' => $inventoryDataClosing->closing_stocks,
            ]);
        });
    }
}
