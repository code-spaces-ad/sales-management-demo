<?php

/**
 * 受注伝票用クエリービルダー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\EloquentBuilders\Receive;

use Illuminate\Database\Eloquent\Builder;

/**
 * 受注伝票用クエリービルダー
 */
class OrdersReceivedBuilder extends Builder
{
    /**
     * 指定した検索条件だけに限定するクエリー
     *
     * @param array $conditions
     * @return Builder
     */
    public function searchCondition(array $conditions): Builder
    {
        return $this
            // IDで絞り込み
            ->when(isset($conditions['id']), function () use ($conditions) {
                return $this->ordersReceivedId($conditions['id']);
            })
            // 受注日で絞り込み
            ->searchOrderDate($conditions['order_date'])
            // 担当者IDで絞り込み
            ->when(isset($conditions['employee_id']), function () use ($conditions) {
                return $this->employeeId($conditions['employee_id']);
            })
            // 得意先IDで絞り込み
            ->when(isset($conditions['customer_id']), function () use ($conditions) {
                return $this->customerId($conditions['customer_id']);
            })
            // 支所IDで絞り込み
            ->when(isset($conditions['branch_id']), function () use ($conditions) {
                return $this->branchId($conditions['branch_id']);
            })
            // 納品先IDで絞り込み
            ->when(isset($conditions['recipient_id']), function () use ($conditions) {
                return $this->recipientId($conditions['recipient_id']);
            })

            // 状態で絞り込み
            ->when(isset($conditions['undelivered_only']), function () use ($conditions) {
                return $this->orderStatus($conditions['undelivered_only']);
            });
    }

    /**
     * 指定したIDのレコードだけに限定するクエリー
     *
     * @param int $target_id
     * @return Builder
     */
    public function ordersReceivedId(int $target_id): Builder
    {
        return $this->where('id', $target_id);
    }

    /**
     * 指定した受注日付のレコードだけに限定するクエリー(範囲)
     *
     * @param string|null $target_order_date_start
     * @param string|null $target_order_date_end
     * @return Builder
     */
    public function orderDate(?string $target_order_date_start, ?string $target_order_date_end): Builder
    {
        if (is_null($target_order_date_start) && is_null($target_order_date_end)) {
            return $this;
        }

        if (isset($target_order_date_start) && is_null($target_order_date_end)) {
            return $this->where('order_date', '>=', $target_order_date_start);
        }

        if (is_null($target_order_date_start) && isset($target_order_date_end)) {
            return $this->where('order_date', '<=', $target_order_date_end);
        }

        return $this->whereBetween('order_date', [$target_order_date_start, $target_order_date_end]);
    }

    /**
     * 指定した納品日付のレコードだけに限定するクエリー(範囲)
     *
     * @param string|null $target_delivery_date_start
     * @param string|null $target_delivery_date_end
     * @return Builder
     */
    public function deliveryDate(?string $target_delivery_date_start, ?string $target_delivery_date_end): Builder
    {
        if (is_null($target_delivery_date_start) && is_null($target_delivery_date_end)) {
            return $this;
        }

        if (isset($target_delivery_date_start) && is_null($target_delivery_date_end)) {
            return $this->where('orders_received_details.delivery_date', '>=', $target_delivery_date_start);
        }

        if (is_null($target_delivery_date_start) && isset($target_delivery_date_end)) {
            return $this->where('orders_received_details.delivery_date', '<=', $target_delivery_date_end);
        }

        return $this->whereBetween('orders_received_details.delivery_date', [$target_delivery_date_start, $target_delivery_date_end]);
    }

    /**
     * 指定した担当者IDのレコードだけに限定するクエリー
     *
     * @param int $target_employee_id
     * @return Builder
     */
    public function employeeId(int $target_employee_id): Builder
    {
        return $this->where('employee_id', $target_employee_id);
    }

    /**
     * 指定した得意先IDのレコードだけに限定するクエリー
     *
     * @param int $target_customer_id
     * @return Builder
     */
    public function customerId(int $target_customer_id): Builder
    {
        return $this->where('customer_id', $target_customer_id);
    }

    /**
     * 指定した支所IDのレコードだけに限定するクエリー
     *
     * @param int $target_branch_id
     * @return Builder
     */
    public function branchId(int $target_branch_id): Builder
    {
        return $this->where('branch_id', $target_branch_id);
    }

    /**
     * 指定した納品先IDのレコードだけに限定するクエリー
     *
     * @param int $target_recipient_id
     * @return Builder
     */
    public function recipientId(int $target_recipient_id): Builder
    {
        return $this->where('recipient_id', $target_recipient_id);
    }

    /**
     * 指定した状態(未納品)のレコードだけに限定するクエリー
     *
     * @param int $target_order_status
     * @return Builder
     */
    public function orderStatus(int $target_order_status): Builder
    {
        return $this->where('order_status', '<>', $target_order_status);
    }

    /**
     * 指定した日付のレコードだけに限定するクエリー
     *
     * @param array|null $target_order_date
     * @return Builder
     */
    public function searchOrderDate(?array $target_order_date): Builder
    {
        return $this
            ->when(!empty($target_order_date), function () use ($target_order_date) {
                return $this->orderDate(
                    $target_order_date['start'] ?? null,
                    $target_order_date['end'] ?? null
                );
            });
    }
}
