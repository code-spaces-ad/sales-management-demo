<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Database\Factories\Inventory;

use App\Enums\InventoryType;
use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryDataClosing;
use App\Models\Inventory\InventoryDataDetail;
use App\Models\Inventory\InventoryDataStatusHistory;
use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use App\Models\Sale\SalesOrderDetail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryDataFactory extends Factory
{
    protected $model = InventoryData::class;

    public function definition()
    {
        // 入出庫日付
        $inout_date = Carbon::now()->format('Y/m/d');
        // 状態
        $inout_status = 1;
        // 担当者ID
        $employee_id = 2;
        // 備考
        $note = '※factoryにて生成。';

        $offices = MasterOfficeFacility::whereBetween('id', [1, 1])->get();
        $office = $offices->random();

        $type = $this->faker->numberBetween(1, 4);

        // 仕入
        if ($type === 1) {
            // 移動元倉庫ID
            $from_warehouse_id = InventoryType::INVENTORY_IN;
            // 移動先倉庫ID
            $to_warehouse_id = $office->id;
        }
        // 納品
        if ($type === 2) {
            // 移動元倉庫ID
            $from_warehouse_id = $office->id;
            // 移動先倉庫ID
            $to_warehouse_id = InventoryType::INVENTORY_OUT;
        }
        // 移動
        if ($type === 3) {
            $offices2 = MasterOfficeFacility::whereBetween('id', [2, 3])->get();
            $office2 = $offices2->random();
            // 移動元倉庫ID
            $from_warehouse_id = $office->id;
            // 移動先倉庫ID
            $to_warehouse_id = $office2->id;
        }
        // 在庫調整
        if ($type === 4) {
            // 移動元倉庫ID
            $from_warehouse_id = InventoryType::INVENTORY_ADJUST;
            // 移動先倉庫ID
            $to_warehouse_id = $office->id;
            // 担当者ID
            $employee_id = 99999;
        }

        return [
            // 入出庫日付
            'inout_date' => $inout_date,
            // 状態
            'inout_status' => $inout_status,
            // 移動元倉庫ID
            'from_warehouse_id' => $from_warehouse_id,
            // 移動先倉庫ID
            'to_warehouse_id' => $to_warehouse_id,
            // 担当者ID
            'employee_id' => $employee_id,
            // 更新者ID
            'updated_id' => 1,
            // 備考
            'note' => $note,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (InventoryData $inventoryData) {
            $faker = $this->faker;
            $countDetail = $faker->numberBetween(1, 3);

            // 1件～3件の SalesOrderDetail を作成
            for ($count = 1; $count <= $countDetail; $count++) {
                InventoryDataDetail::factory()->create([
                    'inventory_data_id' => $inventoryData->id,
                    'sort' => $count,
                ]);
            }

            $isProcess = false;

            // 作成した InventoryDataDetail のデータを取得
            $inventory_data_details = InventoryDataDetail::where('inventory_data_id', $inventoryData->id)->get();

            // 仕入
            if ($inventoryData->from_warehouse_id === InventoryType::INVENTORY_IN) {
                foreach ($inventory_data_details as $detail) {
                    $quantity = $this->faker->numberBetween(2, 5);
                    InventoryDataDetail::where('inventory_data_id', $detail->inventory_data_id)
                        ->where('product_id', $detail->product_id)
                        ->where('sort', $detail->sort)
                        ->update(['quantity' => $quantity]);

                    $inventoryStockData = InventoryStockData::where('warehouse_id', $inventoryData->to_warehouse_id)
                        ->where('product_id', $detail->product_id)
                        ->first();

                    $inventoryStockData->update([
                        'inventory_stocks' => $inventoryStockData->inventory_stocks + $quantity,
                    ]);
                }
                $isProcess = true;
            }

            // 納品
            if ($inventoryData->to_warehouse_id === InventoryType::INVENTORY_OUT) {
                foreach ($inventory_data_details as $detail) {
                    $quantity = $this->faker->numberBetween(-5, -2);
                    InventoryDataDetail::where('inventory_data_id', $detail->inventory_data_id)
                        ->where('product_id', $detail->product_id)
                        ->where('sort', $detail->sort)
                        ->update(['quantity' => $quantity]);

                    $inventoryStockData = InventoryStockData::where('warehouse_id', $inventoryData->from_warehouse_id)
                        ->where('product_id', $detail->product_id)
                        ->first();

                    $inventoryStockData->update([
                        'inventory_stocks' => $inventoryStockData->inventory_stocks + $quantity,
                    ]);
                }
                $isProcess = true;
            }

            // 在庫調整
            if ($inventoryData->from_warehouse_id === InventoryType::INVENTORY_ADJUST) {
                foreach ($inventory_data_details as $detail) {
                    $quantity = $this->faker->numberBetween(-5, 5);
                    InventoryDataDetail::where('inventory_data_id', $detail->inventory_data_id)
                        ->where('product_id', $detail->product_id)
                        ->where('sort', $detail->sort)
                        ->update(['quantity' => $quantity]);

                    $inventoryStockData = InventoryStockData::where('warehouse_id', $inventoryData->to_warehouse_id)
                        ->where('product_id', $detail->product_id)
                        ->first();

                    $product = MasterProduct::where('id', $detail->product_id)->first();

                    $stock = $inventoryStockData->inventory_stocks + (($quantity ?? 0) * -1);
                    $inventoryStockData->update([
                        'inventory_stocks' => $stock,
                        'purchase_total_price' => $stock * $product->purchase_unit_price ?? 0
                    ]);
                }
                $isProcess = true;
            }

            // 移動
            if (!$isProcess
                && $inventoryData->from_warehouse_id < InventoryType::INVENTORY_ADJUST
                && $inventoryData->to_warehouse_id < InventoryType::INVENTORY_ADJUST
            ) {
                foreach ($inventory_data_details as $detail) {
                    $quantity = $this->faker->numberBetween(2, 5);
                    InventoryDataDetail::where('inventory_data_id', $detail->inventory_data_id)
                        ->where('product_id', $detail->product_id)
                        ->where('sort', $detail->sort)
                        ->update(['quantity' => $quantity]);

                    $fromInventoryStockData = InventoryStockData::where('warehouse_id', $inventoryData->from_warehouse_id)
                        ->where('product_id', $detail->product_id)
                        ->first();

                    $toInventoryStockData = InventoryStockData::where('warehouse_id', $inventoryData->to_warehouse_id)
                        ->where('product_id', $detail->product_id)
                        ->first();

                    $fromInventoryStockData->update([
                        'inventory_stocks' => $fromInventoryStockData->inventory_stocks - $quantity,
                    ]);

                    $toInventoryStockData->update([
                        'inventory_stocks' => $toInventoryStockData->inventory_stocks + $quantity,
                    ]);
                }
            }
        });
    }
}
