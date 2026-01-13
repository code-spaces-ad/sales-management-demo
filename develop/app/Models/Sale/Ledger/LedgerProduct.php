<?php

/**
 * 商品台帳用モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Sale\Ledger;

use App\Enums\OrderType;
use App\Models\Sale\SalesOrder;
use App\Models\Trading\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 商品台帳用モデル
 */
class LedgerProduct extends Model
{
    // region static method

    /**
     * 伝票データ取得
     *
     * @param array $search_condition 検索項目
     * @param string $sort
     * @return Collection
     */
    public static function getOrder(array $search_condition, string $sort = 'desc'): Collection
    {
        // 売上伝票と仕入伝票を結合
        $order_data = self::getSalesOrder($search_condition)
            ->concat(self::getPurchaseOrder($search_condition));

        // ソート
        if ($sort === 'desc') {
            $order_data = $order_data->sortByDesc(function ($item) {
                return [$item['order_date'], $item['order_number'], $item['created_at']];
            });
        }
        if ($sort === 'asc') {
            $order_data = $order_data->sortBy(function ($item) {
                return [$item['order_date'], $item['order_number'], $item['created_at']];
            });
        }

        return $order_data;
    }

    /**
     * 売上伝票データ取得
     *
     * @param array $search_condition 検索項目
     * @return Collection
     */
    public static function getSalesOrder(array $search_condition): Collection
    {
        $target_product_id = $search_condition['product_id'] ?? null; // 商品ID
        $target_order_date = $search_condition['order_date'] ?? null; // 伝票日付

        $arr_select_column = [
            /** 伝票ID */
            DB::raw('sales_orders.id AS order_id'),
            /** 伝票日付 */
            DB::raw('sales_orders.order_date AS order_date'),
            /** 伝票番号 */
            DB::raw('sales_orders.order_number AS order_number'),
            /** 種別 */
            DB::raw('cast(' . OrderType::SALES . ' AS UNSIGNED) AS order_kind'),
            /** 得意先ID */
            DB::raw('sales_orders.customer_id AS customer_id'),
            /** 支所ID */
            DB::raw('sales_orders.branch_id AS branch_id'),
            /** 商品名 */
            DB::raw('sales_order_details.product_name AS product_name'),
            /** 単位 */
            DB::raw('sales_order_details.unit_name'),
            /** 単価 */
            DB::raw('sales_order_details.unit_price'),
            /** 単価小数桁数 */
            DB::raw('m_products.unit_price_decimal_digit'),
            /** 数量 */
            DB::raw('sales_order_details.quantity'),
            /** 数量小数桁数 */
            DB::raw('m_products.quantity_decimal_digit'),
            /** 備考 */
            DB::raw('sales_order_details.note'),
            /** 作成日時 */
            DB::raw('sales_orders.created_at'),
        ];

        return SalesOrder::query()
            ->select($arr_select_column)
            ->with(['mCustomer', 'mBranch'])
            ->SalesOrderDetailJoin($target_product_id)
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->when(isset($target_product_id), function ($query) use ($target_product_id) {
                // 商品IDで絞り込み
                return $query->where('sales_order_details.product_id', $target_product_id);
            })
            ->when(isset($target_order_date), function ($query) use ($target_order_date) {
                // 伝票日付で絞り込み
                return $query->orderDate($target_order_date['start'] ?? null, $target_order_date['end'] ?? null);
            })
            ->get();
    }

    /**
     * 仕入伝票データ取得
     *
     * @param array $search_condition 検索項目
     * @return Collection
     */
    public static function getPurchaseOrder(array $search_condition): Collection
    {
        $target_product_id = $search_condition['product_id'] ?? null; // 商品ID
        $target_order_date = $search_condition['order_date'] ?? null; // 伝票日付

        $arr_select_column = [
            /** 伝票ID */
            DB::raw('purchase_orders.id AS order_id'),
            /** 伝票日付 */
            DB::raw('purchase_orders.order_date AS order_date'),
            /** 伝票番号 */
            DB::raw('purchase_orders.order_number AS order_number'),
            /** 種別 */
            DB::raw('cast(' . OrderType::PURCHASE . ' AS UNSIGNED) AS order_kind'),
            /** 仕入先ID */
            DB::raw('purchase_orders.supplier_id AS supplier_id'),
            /** 商品名 */
            DB::raw('purchase_order_details.product_name AS product_name'),
            /** 単位 */
            DB::raw('purchase_order_details.unit_name'),
            /** 単価 */
            DB::raw('purchase_order_details.unit_price'),
            /** 単価小数桁数 */
            DB::raw('m_products.unit_price_decimal_digit'),
            /** 数量 */
            DB::raw('purchase_order_details.quantity'),
            /** 数量小数桁数 */
            DB::raw('m_products.quantity_decimal_digit'),
            /** 備考 */
            DB::raw('purchase_order_details.note'),
            /** 作成日時 */
            DB::raw('purchase_orders.created_at'),
        ];

        return PurchaseOrder::query()
            ->select($arr_select_column)
            ->with(['mSupplier'])
            ->PurchaseOrderDetailJoin($target_product_id)
            ->leftJoin('m_products', 'purchase_order_details.product_id', '=', 'm_products.id')
            ->when(isset($target_product_id), function ($query) use ($target_product_id) {
                // 商品IDで絞り込み
                return $query->where('purchase_order_details.product_id', $target_product_id);
            })
            ->when(isset($target_order_date), function ($query) use ($target_order_date) {
                // 伝票日付で絞り込み
                return $query->orderDate($target_order_date['start'] ?? null, $target_order_date['end'] ?? null);
            })
            ->get();
    }

    /**
     * コレクション型にページネーションを設定
     *
     * @param Collection $order_data
     * @return LengthAwarePaginator
     */
    public static function setPaginateForCollection(Collection $order_data): LengthAwarePaginator
    {
        return self::paginate(
            $order_data,
            config('consts.default.sales_order.page_count'),
            null,
            [
                'path' => route('sale.ledger.products'),
            ]
        );
    }

    /**
     * ページネーション
     *
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

    // endregion static method
}
