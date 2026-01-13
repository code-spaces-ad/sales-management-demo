<?php

/**
 * 仕入先マスタ用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Master;

use App\Helpers\MasterIntegrityHelper;
use App\Models\Master\MasterSupplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 仕入先マスタ用リポジトリ
 */
class SupplierRepository
{
    protected Model $model;

    /**
     * インスタンス化
     *
     * @param MasterSupplier $model
     */
    public function __construct(MasterSupplier $model)
    {
        $this->model = $model;
    }

    /**
     * 検索結果を取得（ページネーション）
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public function getSearchResultPagenate(array $search_condition_input_data): LengthAwarePaginator
    {
        return $this->model->query()
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.master.Suppliers.page_count'));
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
     * 仕入先新規登録
     *
     * @param array $array
     * @return Model
     */
    public function createSupplier(array $array): Model
    {
        return $this->model->query()->create($array);
    }

    /**
     * 仕入先更新
     *
     * @param Model $supplier
     * @param array $array
     * @return Model
     */
    public function updateSupplier(Model $supplier, array $array): Model
    {
        return tap($supplier)->update($array);
    }

    /**
     * 仕入先削除
     *
     * @param Model $supplier
     * @return bool|null
     */
    public function deleteSupplier(Model $supplier): ?bool
    {
        return $supplier->delete();
    }

    /**
     * 住所1+住所2を取得
     *
     * @return string 住所
     */
    public function getAddressAttribute(): string
    {
        return ($this->address1 ?? '') . ($this->address2 ?? '');
    }

    /**
     * 仕入先データを取得
     *
     * @param int|null $exclude_Supplier_id 除外する仕入先ID
     * @return Collection
     */
    public function getBillingSupplier(?int $exclude_supplier_id = null): Collection
    {
        return $this->model->query()
            ->when($exclude_supplier_id !== null, function ($query) use ($exclude_supplier_id) {
                return $query->where('id', '<>', $exclude_supplier_id);
            })
            ->oldest('code')
            ->get();
    }

    /**
     * 仕入締対象の仕入先データを取得
     *
     * @return Collection
     */
    public function getClosingBillingSupplier(): Collection
    {
        $billing_supplier_ids = MasterSupplier::select(DB::raw('COALESCE(supplier_id, id) AS id'))
            ->groupBy(DB::raw('COALESCE(supplier_id, id)'))->pluck('id')->toarray();

        return $this->model->query()
            ->whereIn('id', $billing_supplier_ids)
            ->oldest('code')
            ->get();
    }

    /**
     * マスタの使用状況を返す
     *
     * @return bool
     */
    public function getUseMasterAttribute(): bool
    {
        return MasterIntegrityHelper::existsUseMasterSupplier($this->id);
    }
}
