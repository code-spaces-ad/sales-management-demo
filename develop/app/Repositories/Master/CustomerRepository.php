<?php

/**
 * 得意先マスタ用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Master;

use App\Helpers\MasterIntegrityHelper;
use App\Models\Master\MasterCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 得意先マスタ用リポジトリ
 */
class CustomerRepository
{
    protected Model $model;

    /**
     * インスタンス化
     *
     * @param MasterCustomer $model
     */
    public function __construct(MasterCustomer $model)
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
            ->paginate(config('consts.default.master.customers.page_count'));
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
     * 得意先新規登録
     *
     * @param array $array
     * @return Model
     */
    public function createCustomer(array $array): Model
    {
        // 詳細登録の為、現在のモデルをインスタンス
        return $this->model = $this->model->query()->create($array);
    }

    /**
     * 得意先_敬称リレーション
     *
     * @param array $array
     * @return Model
     */
    public function createCustomerHonorificTitle(array $array): Model
    {
        $this->model->mCustomerHonorificTitle()->create($array);

        return $this->model;
    }

    /**
     * 得意先更新
     *
     * @param Model $customer
     * @param array $array
     * @return Model
     */
    public function updateCustomer(Model $customer, array $array): Model
    {
        return tap($customer)->update($array);
    }

    /**
     * 得意先_敬称リレーション更新
     *
     * @param Model $customer
     * @param array $array
     * @return Model
     */
    public function updateCustomerHonorificTitle(Model $customer, array $array): Model
    {
        $customer->mCustomerHonorificTitle()
            ->updateOrCreate(
                ['customer_id' => $customer->id],
                $array
            );

        return $customer;
    }

    /**
     * 得意先削除
     *
     * @param Model $customer
     * @return bool|null
     */
    public function deleteCustomer(Model $customer): ?bool
    {
        $customer->mCustomerHonorificTitle()->delete();

        return $customer->delete();
    }

    /**
     * 請求先データを取得
     *
     * @param int|null $exclude_customer_id 除外する得意先ID
     * @return Collection
     */
    public function getBillingCustomer(?int $exclude_customer_id = null): Collection
    {
        return $this->model->query()
            ->when($exclude_customer_id !== null, function ($query) use ($exclude_customer_id) {
                return $query->where('id', '<>', $exclude_customer_id);
            })
            ->oldest('code')
            ->get();
    }

    /**
     * 請求締対象の請求先データを取得
     *
     * @return Collection
     */
    public function getClosingBillingCustomer(): Collection
    {
        $billing_customer_ids = MasterCustomer::select(DB::raw('COALESCE(billing_customer_id, id) AS id'))
            ->groupBy(DB::raw('COALESCE(billing_customer_id, id)'))->pluck('id')->toarray();

        return $this->model->query()
            ->whereIn('id', $billing_customer_ids)
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
        return MasterIntegrityHelper::existsUseMasterCustomer($this->id);
    }

    /**
     * 得意先モデルを返す
     *
     * @param int $id
     * @return Model
     */
    public function getCustomer(int $id): Model
    {
        return $this->model->query()->find($id);
    }

    /**
     * 請求先IDを返す
     *
     * @param int $id
     * @return int
     */
    public function getBillingCustomerId(int $id): int
    {
        return $this->getCustomer($id)->billing_customer_id;
    }

    /**
     * 税計算区分を返す
     *
     * @param int $id
     * @return int
     */
    public function getTaxCalcTypeId(int $id): int
    {
        return $this->getCustomer($id)->tax_calc_type_id;
    }

    /**
     * 消費税端数処理方法を返す
     *
     * @param int $id
     * @return int
     */
    public function getTaxRoundingMethodId(int $id): int
    {
        return $this->getCustomer($id)->tax_rounding_method_id;
    }
}
