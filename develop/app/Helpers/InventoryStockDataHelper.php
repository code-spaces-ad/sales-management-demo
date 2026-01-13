<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterWarehouse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * 在庫調整画面表示用ヘルパークラス
 */
class InventoryStockDataHelper
{
    /**
     * 倉庫毎に商品マスタにある商品全てを返す
     *
     * @param array $search_condition
     * @param bool $excel_export
     * @return Collection
     */
    public static function getSearchResult(array $search_condition, bool $excel_export = false): Collection
    {
        $inventory_stock_data = InventoryStockData::query()->get();
        $stock_data = [];
        foreach ($inventory_stock_data as $key => $val) {
            $stock_data[$val->product_id][$val->warehouse_id]['stock'] = $val->inventory_stocks;
            $stock_data[$val->product_id][$val->warehouse_id]['price'] = $val->purchase_total_price;
        }

        $products = MasterProduct::getProductInventoryStock($search_condition);
        $wareHouses = MasterWarehouse::getDoControlWarehouseData(); // 在庫管理する倉庫のみ取得

        if (isset($search_condition['warehouse_id'])) {
            $wareHouses = MasterWarehouse::query()->where('id', $search_condition['warehouse_id'])->get();
        }

        $search_result = [];
        $index = 0;
        foreach ($products as $product) {
            foreach ($wareHouses as $wareHouse) {
                $stock = '0';
                $price = '0';
                if (!empty($stock_data[$product->id][$wareHouse->id]['stock'])) {
                    $stock = $stock_data[$product->id][$wareHouse->id]['stock'];
                }

                if (!empty($stock_data[$product->id][$wareHouse->id]['price'])) {
                    $price = $stock_data[$product->id][$wareHouse->id]['price'];
                }

                if ($excel_export) {
                    $search_result[$index]['warehouse_name'] = $wareHouse->name;
                    $search_result[$index]['product_name'] = $product->name;
                    $search_result[$index]['stock'] = $stock;

                    ++$index;

                    continue;
                }

                $search_result[$index]['product_id'] = $product->id;
                $search_result[$index]['product_code_zerofill'] = $product->code_zerofill;
                $search_result[$index]['warehouse_id'] = $wareHouse->id;
                $search_result[$index]['warehouse_code_zerofill'] = $wareHouse->code_zerofill;
                $search_result[$index]['product_name'] = $product->name;
                $search_result[$index]['warehouse_name'] = $wareHouse->name;
                $search_result[$index]['stock'] = $stock;
                $search_result[$index]['purchase_total_price'] = $price;

                ++$index;
            }
        }

        return collect($search_result);
    }

    /**
     * 倉庫毎に商品マスタにある商品全てを返す
     *
     * @param array $search_condition
     * @return LengthAwarePaginator|null
     */
    public static function getInventoryStockData(array $search_condition): ?LengthAwarePaginator
    {
        $coll = self::getSearchResult($search_condition);

        return self::paginate(
            $coll,
            config('consts.default.sales_order.page_count'),
            null,
            [
                'path' => route('inventory.inventory_stock_datas.index'),
            ]
        );
    }

    /**
     * @param $items
     * @param int $perPage
     * @param null $page
     * @param array $options
     * @return LengthAwarePaginator
     */
    public static function paginate($items, int $perPage = 5, $page = null, array $options = []): LengthAwarePaginator
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
