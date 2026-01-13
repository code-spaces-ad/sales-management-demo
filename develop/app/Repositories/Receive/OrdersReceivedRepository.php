<?php

/**
 * 受注伝票用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Receive;

use App\Enums\SalesConfirm;
use App\Models\Receive\OrdersReceived;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 受注伝票用リポジトリ
 */
class OrdersReceivedRepository
{
    protected Model $model;

    /**
     * 受注伝票モデルをインスタンス
     *
     * @param OrdersReceived $model
     */
    public function __construct(OrdersReceived $model)
    {
        $this->model = $model;
    }

    /**
     * 検索結果を取得(ページネーション)
     *
     * @param array $input_data
     * @return LengthAwarePaginator
     */
    public function getSearchResult(array $input_data): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['mCustomer', 'mEmployee', 'mUpdated', 'ordersReceivedDetail'])
            ->orderByDesc('order_number') // 受注番号の降順
            ->searchCondition($input_data)
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * 検索結果を取得
     *
     * @param array $search_condition_input_data
     * @param string $sort
     * @return Collection
     */
    public function getOrderReceivedResult(array $search_condition_input_data, string $sort = 'desc'): Collection
    {
        return $this->model->query()
            ->with(['mCustomer', 'mEmployee', 'mUpdated', 'ordersReceivedDetail'])
            ->searchCondition($search_condition_input_data)
            ->when($sort === 'desc', function ($query) {
                // 作成日時の降順
                return $query
                    ->latest('order_number')
                    ->latest('order_date')
                    ->oldest();
            })
            ->when($sort === 'asc', function ($query) {
                // 作成日時の昇順
                return $query
                    ->oldest('order_number')
                    ->oldest('order_date')
                    ->latest();
            })
            ->get();
    }

    /**
     * 売上確定フラグが立っている対象の受注詳細のみ取得
     *
     * @param array $sorts
     * @return Collection
     */
    public function getDetailBySalesConfirm(array $sorts = []): Collection
    {
        return $this->model->ordersReceivedDetail()
            // 売上確定フラグで絞り込み
            ->where('sales_confirm', SalesConfirm::CONFIRM)
            // ソートNoで絞り込み
            ->when(!empty($sorts), function ($query) use ($sorts) {
                return $query->whereIn('sort', $sorts);
            })
            ->get();
    }

    /**
     * 受注伝票新規登録
     *
     * @param array $array
     * @return Model
     */
    public function createOrdersReceived(array $array): Model
    {
        // 詳細登録の為、現在のモデルをインスタンス
        return $this->model = $this->model->query()->create($array);
    }

    /**
     * 受注伝票詳細新規登録
     *
     * @param array $details
     * @return Model
     */
    public function createOrdersReceivedDetails(array $details): Model
    {
        return tap($this->model, function ($model) use ($details) {
            $model->ordersReceivedDetail()->createMany($details);
        });
    }

    /**
     * 受注伝票更新
     *
     * @param OrdersReceived $target_data
     * @param array $array
     * @return Model
     */
    public function updateOrdersReceived(OrdersReceived $target_data, array $array): Model
    {
        return tap($target_data)->update($array);
    }

    /**
     * 受注伝票詳細更新
     *
     * @param array $detail
     * @return Model
     */
    public function updateOrdersReceivedDetails(Model $target_data, array $detail): Model
    {
        // 受注伝票詳細を更新
        return tap($target_data, function ($model) use ($detail) {
            $model->ordersReceivedDetail()
                ->updateOrInsert(
                    [
                        'orders_received_id' => $model->id,
                        'sort' => $detail['sort'],
                    ],
                    $detail
                );
        });
    }

    /**
     * 更新に伴う受注伝票詳細削除
     *
     * @param Model $target_data
     * @param array $sorts
     * @return Model
     */
    public function deleteOrderDetailsForUpdate(Model $target_data, array $sorts): Model
    {
        return tap($target_data, function ($model) use ($sorts) {
            $model->ordersReceivedDetail()
                ->whereNotIn('sort', $sorts)
                ->delete();
        });
    }

    /**
     * 受注伝票削除
     *
     * @param OrdersReceived $target_data
     * @return bool|null
     */
    public function deleteOrdersReceived(OrdersReceived $target_data): ?bool
    {
        $target_data->ordersReceivedDetail()->delete();

        return $target_data->delete();
    }

    /**
     * 未納品の明細があるかの判定
     *
     * @param Model $target_data
     * @return bool
     */
    public function getDeliveryDateFlg(Model $target_data): bool
    {
        return $target_data
            ->ordersReceivedDetail()
            ->whereNull('delivery_date')
            ->exists();
    }

    /**
     * 売上確定セット
     *
     * @param Model $target_data
     * @return void
     */
    public function setSalesConfirm(Model $target_data): void
    {
        $target_data->order_status = SalesConfirm::CONFIRM;
        $target_data->save();
    }

    /**
     * 伝票番号を指定して該当する受注データが存在するかを返す
     *
     * @param int $order_number
     * @return bool
     */
    public function existByOrderNumber(int $order_number): bool
    {
        return $this->model->query()
            ->where('order_number', $order_number)
            ->exists();
    }
}
