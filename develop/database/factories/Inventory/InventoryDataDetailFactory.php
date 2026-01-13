<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Database\Factories\Inventory;

use App\Enums\InventoryType;
use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryDataDetail;
use App\Models\Master\MasterProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryDataDetailFactory extends Factory
{
    protected $model = InventoryDataDetail::class;

    public function definition()
    {
        // 在庫データ取得（もしくは作成）
        $inventoryData = InventoryData::inRandomOrder()->first();

        // 商品情報取得
        $maxId = MasterProduct::max('id');
        $minId = MasterProduct::min('id');

        $products = MasterProduct::whereBetween('id', [$minId, $maxId])->get();
        $product = $products->random();

        return [
            // 在庫データID
            'inventory_data_id' => $inventoryData->id,
            // 商品ID
            'product_id' => $product->id,
            // 商品名
            'product_name' => $product->name,
            // 数量（外で更新すること）
            'quantity' => 10,
            // 備考
            'note' => '※factoryにて生成。',
            // ソート
            'sort' => 1,
        ];
    }
}
