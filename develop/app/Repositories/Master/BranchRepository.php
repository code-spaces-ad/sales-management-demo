<?php

/**
 * 支所マスタ用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Master;

use App\Helpers\MasterIntegrityHelper;
use App\Models\Master\MasterBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 支所マスタ用リポジトリ
 */
class BranchRepository
{
    protected Model $model;

    /**
     * インスタンス化
     *
     * @param MasterBranch $model
     */
    public function __construct(MasterBranch $model)
    {
        $this->model = $model;
    }

    /**
     * 検索結果を取得 (ページネーション)
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public function getSearchResultPagenate(array $search_condition_input_data): LengthAwarePaginator
    {
        return $this->model->query()
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.master.branches.page_count'));
    }

    /**
     * 検索結果を取得
     *
     * @param array $search_condition_input_data
     * @return Collection
     */
    public function getSearchResult(array $search_condition_input_data): Collection
    {
        return $this->model->query()
            ->searchCondition($search_condition_input_data)
            ->get();
    }

    /**
     * 支所新規登録
     *
     * @param array $array
     * @return Model
     */
    public function createBranch(array $array): Model
    {
        return $this->model->query()->create($array);
    }

    /**
     * 支所更新
     *
     * @param Model $branch
     * @param array $array
     * @return Model
     */
    public function updateBranch(Model $branch, array $array): Model
    {
        return tap($branch)->update($array);
    }

    /**
     * 支所削除
     *
     * @param Model $branch
     * @return bool|null
     */
    public function deleteBranch(Model $branch): ?bool
    {
        return $branch->delete();
    }

    /**
     * 得意先を取得
     *
     * @return string
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->mCustomer->name ?? '';
    }

    /**
     * マスタの使用状況を返す
     *
     * @return bool
     */
    public function getUseMasterAttribute(): bool
    {
        return MasterIntegrityHelper::existsUseMasterBranch($this->id);
    }
}
