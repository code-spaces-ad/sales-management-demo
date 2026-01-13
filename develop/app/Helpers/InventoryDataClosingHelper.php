<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryDataClosing;
use App\Models\Inventory\InventoryStockData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 締在庫数用ヘルパークラス
 */
class InventoryDataClosingHelper
{
    /**
     * 締在庫数を登録
     *
     * @param string $order_date
     * @return void
     */
    public static function registInventoryDataClosing(string $order_date): void
    {
        $closing_ym = DateHelper::changeDateFormat($order_date, 'Ym');

        $inventory_stocks_data = InventoryStockData::query()
            ->oldest('warehouse_id')
            ->oldest('id')
            ->get();

        foreach ($inventory_stocks_data ?? [] as $detail) {
            self::updateOrInsertInventoryDataClosing($detail, $closing_ym);
        }

    }

    /**
     * 締在庫数をupsert
     *
     * @param $detail
     * @param string $closing_ym
     * @return Model
     */
    public static function updateOrInsertInventoryDataClosing($detail, string $closing_ym): Model
    {
        $inventory_data_closing = new InventoryDataClosing();

        $inventory_data_closing->query()
            ->updateOrInsert(
                [
                    'warehouse_id' => $detail['warehouse_id'],
                    'product_id' => $detail['product_id'],
                    'closing_ym' => $closing_ym,
                ],
                [
                    'closing_stocks' => $detail['inventory_stocks'],
                    'deleted_at' => null,
                ]
            );

        return $inventory_data_closing->query()
            ->firstOrNew([
                'warehouse_id' => $detail['warehouse_id'],
                'product_id' => $detail['product_id'],
                'closing_ym' => $closing_ym,
            ]);
    }

    /**
     * 在庫移動履歴を取得
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public static function getProductMovingResult(array $search_condition_input_data): LengthAwarePaginator
    {
        $target_product_id = $search_condition_input_data['product_id'];
        $target_warehouse_id = $search_condition_input_data['warehouse_id'];
        $target_inout_date = $search_condition_input_data['inout_date'];

        return InventoryData::query()
            ->with(['mWarehouseFrom', 'mWarehouseTo', 'ordersReceived', 'mEmployee'])
            ->leftjoin('inventory_data_details', function ($join) use ($target_product_id) {
                $join->on('inventory_datas.id', '=', 'inventory_data_details.inventory_data_id')
                    ->when(!empty($target_product_id), function ($query) use ($target_product_id) {
                        return $query->where('product_id', $target_product_id);
                    })
                    ->whereNull('inventory_data_details.deleted_at');
            })
            ->when(!empty($target_product_id), function ($query) use ($target_product_id) {
                return $query->where('inventory_data_details.product_id', $target_product_id);
            })
            // 倉庫ID(FromかTo)で絞り込み
            ->fromOrToWarehouseId($target_warehouse_id)
            // 入出庫日で絞り込み
            ->searchInoutDate($target_inout_date)
            ->latest('inventory_datas.inout_date')
            ->latest('inventory_datas.id')
            ->paginate(config('consts.default.sales_order.page_count'));
    }
}
