<?php

/**
 * 現在庫データ用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Inventory;

use App\Models\Inventory\InventoryStockData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 現在庫データ用リポジトリ
 */
class InventoryStockDataRepository
{
    protected Model $model;

    /**
     * 受注伝票モデルをインスタンス
     *
     * @param InventoryStockData $model
     */
    public function __construct(InventoryStockData $model)
    {
        $this->model = $model;
    }

    /**
     * InventoryStockDataモデルを取得
     *
     * @param int $warehouse_id
     * @param int $product_id
     * @return Model
     */
    public function getInventoryStockData(int $warehouse_id, int $product_id): Model
    {
        return $this->model->query()
            ->where('warehouse_id', $warehouse_id)
            ->where('product_id', $product_id)
            ->first();
    }

    /**
     * 対象の現在個数を取得
     *
     * @param int $warehouse_id
     * @param int $product_id
     * @return int
     */
    public function getInventoryStock(int $warehouse_id, int $product_id): int
    {
        return $this->model->query()
            ->where('warehouse_id', $warehouse_id)
            ->where('product_id', $product_id)
            ->value('inventory_stocks');
    }

    /**
     * 検索結果を取得
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public function getSearchResult(array $search_condition_input_data): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['mProduct', 'mWarehouseFrom', 'mWarehouseTo', 'mUpdated'])
            ->orderByDesc('inout_date')   // 入出庫日の降順
            ->orderByDesc('id')           // IDの降順
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * 現在庫データ更新
     *
     * @param array $detail
     * @return Model
     */
    public function updateInventoryStockData(array $detail): Model
    {
        // 現在庫データを更新
        return tap($this->model, function ($model) use ($detail) {
            $model->query()
                ->updateOrInsert(
                    [
                        'warehouse_id' => $detail['warehouse_id'],
                        'product_id' => $detail['product_id'],
                    ],
                    $detail
                );
        });
    }

    /**
     * 現在庫データの存在チェック
     *
     * @param int $warehouse_id
     * @param int $product_id
     * @return bool
     */
    public function existsInventoryStockData(int $warehouse_id, int $product_id): bool
    {
        return $this->model->query()
            ->where('warehouse_id', $warehouse_id)
            ->where('product_id', $product_id)
            ->exists();
    }
}
