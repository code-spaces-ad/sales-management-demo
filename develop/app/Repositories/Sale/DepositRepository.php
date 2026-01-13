<?php

/**
 * 入金伝票用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Sale;

use App\Enums\TransactionType;
use App\Models\Sale\DepositOrder;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 入金伝票用リポジトリ
 */
class DepositRepository
{
    protected Model $model;

    /**
     * 入金伝票モデルをインスタンス
     *
     * @param DepositOrder $model
     */
    public function __construct(DepositOrder $model)
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
            ->orderBy('order_date')
            ->orderBy('order_number')
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.deposit_order.page_count'));
    }

    /**
     * 検索結果を取得（合計値）
     *
     * @param array $search_condition_input_data
     * @return Collection
     */
    public function getDepositTotal(array $search_condition_input_data): Collection
    {
        return $this->model->query()
            ->join('deposit_order_details', 'deposit_orders.id', '=', 'deposit_order_details.deposit_order_id')
            ->searchCondition($search_condition_input_data)
            ->get([
                DB::raw('SUM(deposit) AS deposit'),
                DB::raw('SUM(amount_cash + amount_check + amount_transfer + amount_bill + amount_offset) AS payment'),
                DB::raw('SUM(amount_discount + amount_fee + amount_other) AS adjust_amount_total'),
            ]);
    }

    /**
     * 締対象となる得意先毎の入金伝票情報を取得
     *
     * @param string $customer_id
     * @param string $start_date
     * @param string $end_date
     * @param int $department_id
     * @param int $office_facilities_id
     * @return array
     */
    public function getTargetClosingIds(string $customer_id, string $start_date, string $end_date, int $department_id, int $office_facilities_id): array
    {
        return $this->model->query()
            ->where('transaction_type_id', TransactionType::ON_ACCOUNT)
            ->where('office_facilities_id', $office_facilities_id)
            ->where('billing_customer_id', $customer_id)
            ->where('department_id', $department_id)
            ->where('office_facilities_id', $office_facilities_id)
            ->whereBetween('order_date', [$start_date, $end_date])
            ->whereNull('closing_at')
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * 入金伝票新規登録
     *
     * @param array $array
     * @return Model
     */
    public function createDepositOrder(array $array): Model
    {
        // 詳細登録の為、現在のモデルをインスタンス
        return $this->model = $this->model->query()->create($array);
    }

    /**
     * 入金伝票詳細新規登録
     *
     * @param array $array
     * @return Model
     */
    public function createDepositOrderDetail(array $array): Model
    {
        return tap($this->model, function ($model) use ($array) {
            $model->depositOrderDetail()->create($array);
        });
    }

    /**
     * 入金伝票_手形リレーション新規登録
     *
     * @param array $array
     * @return Model
     */
    public function createDepositOrderBill(array $array): Model
    {
        return tap($this->model, function ($model) use ($array) {
            $model->depositOrderBill()->create($array);
        });
    }

    /**
     * 入金伝票更新
     *
     * @param Model $deposit
     * @param array $array
     * @return Model
     */
    public function updateDepositOrder(Model $deposit, array $array): Model
    {
        return tap($deposit)->update($array);
    }

    /**
     * 入金伝票詳細更新
     *
     * @param Model $deposit
     * @param array $array
     * @return Model
     */
    public function updateDepositOrderDetail(Model $deposit, array $array): Model
    {
        // 入金伝票詳細を更新
        return tap($deposit, function ($model) use ($array) {
            $model->depositOrderDetail()->update($array);
        });
    }

    /**
     * 入金伝票_手形リレーション更新
     *
     * @param array $array
     * @return Model
     */
    public function updateDepositOrderBill(array $array): Model
    {
        // 入金伝票_手形リレーションを更新
        return tap($this->model, function ($model) use ($array) {
            $model->depositOrderBill()
                ->updateOrInsert(
                    [
                        'deposit_order_id' => $array['deposit_order_id'],
                    ],
                    $array
                );
        });
    }

    /**
     * 入金伝票削除
     *
     * @param Model $deposit
     * @return bool|null
     */
    public function deleteDepositOrder(Model $deposit): ?bool
    {
        // 入金伝票詳細削除
        $deposit->depositOrderDetail()->delete();

        // 入金伝票_手形リレーション削除
        $deposit->depositOrderBill()->delete();

        // 入金伝票削除
        return $deposit->delete();
    }

    /**
     * 入金伝票_手形リレーション削除
     *
     * @return bool|null
     *
     * @throws Exception
     */
    public function deleteDepositOrderBill(): ?bool
    {
        return $this->model->depositOrderBill()->delete();
    }
}
