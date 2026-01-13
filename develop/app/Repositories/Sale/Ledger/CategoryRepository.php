<?php

/**
 * 種別累計売上表用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Sale\Ledger;

use App\Enums\Categories;
use App\Models\Sale\Ledger\LedgerCategory;
use App\Models\Sale\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 種別累計売上表用リポジトリ
 */
class CategoryRepository
{
    protected Model $model;

    /**
     * 種別累計売上表モデルをインスタンス
     *
     * @param LedgerCategory $model
     */
    public function __construct(LedgerCategory $model)
    {
        $this->model = $model;
    }

    /**
     * 検索結果を取得(ビルダー)
     *
     * @param array $conditions
     * @return Builder
     */
    public static function getData(array $conditions): Builder
    {
        $select_column = [
            /** 伝票日付 */
            DB::raw('sales_orders.order_date'),
            /** 肥料 */
            DB::raw('SUM(sales_order_details.sub_total * (CASE WHEN m_products.category_id = ' . Categories::FERTILIZER . ' THEN 1 ELSE 0 END)) AS fertilizer'),
            /** 農薬 */
            DB::raw('SUM(sales_order_details.sub_total * (CASE WHEN m_products.category_id = ' . Categories::PESTICIDE . ' THEN 1 ELSE 0 END)) AS pesticide'),
            /** 資材 */
            DB::raw('SUM(sales_order_details.sub_total * (CASE WHEN m_products.category_id = ' . Categories::MATERIAL . ' THEN 1 ELSE 0 END)) AS material'),
            /** 種子 */
            DB::raw('SUM(sales_order_details.sub_total * (CASE WHEN m_products.category_id = ' . Categories::SEED . ' THEN 1 ELSE 0 END)) AS seed'),
            /** その他 */
            DB::raw('SUM(sales_order_details.sub_total * (CASE WHEN m_products.category_id = ' . Categories::ANOTHER . ' THEN 1 ELSE 0 END)) AS another'),
            /** 日計 */
            DB::raw('SUM(sales_order_details.sub_total) AS day_total'),
        ];

        return SalesOrder::query()
            ->select($select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->searchCondition($conditions)
            ->groupBy('sales_orders.order_date')
            ->oldest('sales_orders.order_date');
    }

    /**
     * 検索結果(カテゴリー合計)を取得
     *
     * @param array $conditions
     * @return SalesOrder
     */
    public static function getDataCategoryTotal(array $conditions): SalesOrder
    {
        $select_column = [
            /** 肥料 */
            DB::raw('SUM(sales_order_details.sub_total * (CASE WHEN m_products.category_id = ' . Categories::FERTILIZER . ' THEN 1 ELSE 0 END)) AS fertilizer_total'),
            /** 農薬 */
            DB::raw('SUM(sales_order_details.sub_total * (CASE WHEN m_products.category_id = ' . Categories::PESTICIDE . ' THEN 1 ELSE 0 END)) AS pesticide_total'),
            /** 資材 */
            DB::raw('SUM(sales_order_details.sub_total * (CASE WHEN m_products.category_id = ' . Categories::MATERIAL . ' THEN 1 ELSE 0 END)) AS material_total'),
            /** 種子 */
            DB::raw('SUM(sales_order_details.sub_total * (CASE WHEN m_products.category_id = ' . Categories::SEED . ' THEN 1 ELSE 0 END)) AS seed_total'),
            /** その他 */
            DB::raw('SUM(sales_order_details.sub_total * (CASE WHEN m_products.category_id = ' . Categories::ANOTHER . ' THEN 1 ELSE 0 END)) AS another_total'),
            /** 合計 */
            DB::raw('SUM(sales_order_details.sub_total) AS all_total'),
        ];

        return SalesOrder::query()
            ->select($select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->searchCondition($conditions)
            ->get()
            ->get(0);
    }

    /**
     * 検索結果を取得(ページネーション)
     *
     * @param array $conditions
     * @return LengthAwarePaginator
     */
    public function getSearchResultPaginate(array $conditions): LengthAwarePaginator
    {
        return self::getData($conditions)
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * 検索結果を取得
     *
     * @param array $conditions
     * @return Collection
     */
    public function getSearchResult(array $conditions): Collection
    {
        return self::getData($conditions)
            ->get();
    }
}
