<?php

/**
 * 在庫データ用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Inventory;

use App\Models\Inventory\InventoryData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 在庫データ用リポジトリ
 */
class InventoryRepository
{
    protected Model $model;

    /**
     * 受注伝票モデルをインスタンス
     *
     * @param InventoryData $model
     */
    public function __construct(InventoryData $model)
    {
        $this->model = $model;
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
            ->with(['mProduct', 'mWarehouseFrom', 'mWarehouseTo', 'mUpdated', 'inventoryDataDetail'])
            ->orderByDesc('inout_date')   // 入出庫日の降順
            ->orderByDesc('id')           // IDの降順
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * 在庫データ新規登録
     *
     * @param array $array
     * @return Model
     */
    public function createInventoryData(array $array): Model
    {
        // 詳細登録の為、現在のモデルをインスタンス
        return $this->model = $this->model->query()->create($array);
    }

    /**
     * 在庫データ詳細新規登録
     *
     * @param array $details
     * @return Model
     */
    public function createInventoryDataDetails(array $details): Model
    {
        $this->model->InventoryDataDetail()->createMany($details);

        return $this->model;
    }

    /**
     * 在庫データ更新
     *
     * @param array $array
     * @return Model
     */
    public function updateInventoryData(array $array): Model
    {
        return tap($this->model)
            ->update($array);
    }

    /**
     * 在庫データ詳細更新
     *
     * @param array $detail
     * @param int $sort
     * @return Model
     */
    public function updateInventoryDataDetails(array $detail, int $sort): Model
    {
        // 在庫データ詳細更新
        $this->model->InventoryDataDetail()
            ->updateOrInsert(
                [
                    'inventory_data_id' => $this->model->id,
                    'sort' => $sort,
                ],
                $detail
            );

        return $this->model;
    }

    /**
     * 更新に伴う在庫データ詳細削除
     *
     * @param array $sorts
     * @return Model
     */
    public function deleteInventoryDataDetailsForUpdate(array $sorts): Model
    {
        $this->model->InventoryDataDetail()
            ->whereNotIn('sort', $sorts)
            ->delete();

        return $this->model;
    }

    /**
     * 在庫データ削除
     *
     * @param Model $target_data
     * @return bool|null
     */
    public function deleteInventoryData(Model $target_data): ?bool
    {
        $target_data->inventoryDataDetail()->delete();

        return $target_data->delete();
    }
}
