<?php

/**
 * 年度別販売実績表用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Sale\Ledger;

use App\Enums\Categories;
use App\Models\Master\MasterCategory;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterProduct;
use App\Models\Sale\Ledger\LedgerFiscalYear;
use App\Models\Sale\SalesOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 年度別販売実績表用リポジトリ
 */
class FiscalYearRepository
{
    protected Model $model;

    protected array $select_column = [];

    /**
     * 年度別販売実績表モデルをインスタンス
     *
     * @param LedgerFiscalYear $model
     */
    public function __construct(LedgerFiscalYear $model)
    {
        $this->model = $model;
    }

    /**
     * カラムをセット
     *
     * @param array $conditions
     * @return FiscalYearRepository
     */
    public function setSelectColumn(array $conditions): self
    {
        $this->select_column = [
            /** 伝票月 */
            DB::raw("DATE_FORMAT(sales_orders.order_date, '%Y年%m月') AS order_month"),
            /** 年度合計 */
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ') AS month_total'),
        ];

        return $this;
    }

    /**
     * 検索結果を取得(ビルダー)
     *
     * @param array $conditions
     * @return Builder
     */
    public function getData(array $conditions): Builder
    {
        return SalesOrder::query()
            ->select($this->select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->searchCondition($conditions)
            ->groupBy(DB::raw("DATE_FORMAT(sales_orders.order_date, '%Y年%m月')"))
            ->oldest(DB::raw("DATE_FORMAT(sales_orders.order_date, '%Y年%m月')"))
            ->oldest('m_products.category_id');
    }

    /**
     * 検索結果を取得(月ごとの合計)
     *
     * @param array $conditions
     * @return Collection
     */
    public function getSomeMonthTotal(array $conditions): Collection
    {
        return self::setSalesOrderFilterByOrderDate($conditions)
            // 'Y年m月'形式にformatした伝票日付でgroupBy
            ->groupBy(function ($detail) {
                return (new Carbon($detail->order_date))->format('Y年m月');
            })
            // 指定されたaggregation_type(sales_order_detailのカラムのいずれか)の合計を算出
            ->map(function ($details) use ($conditions) {
                return $details->sum(function ($detail) use ($conditions) {
                    return $detail->getSumAnyColumnBySalesOrderDetail($conditions['aggregation_type']);
                });
            })
            // Collectionの構造を変換
            ->map(function ($item, $key) {
                return [
                    'order_month' => $key,
                    'month_total' => $item,
                ];
            })
            // キーを排除した状態に変換
            ->values();
    }

    /**
     * 検索結果を取得(ビルダー)
     *
     * @param array $conditions
     * @return Builder
     */
    public function getDataCategory(array $conditions): Builder
    {
        $select_column = [
            /** カテゴリーID */
            DB::raw('m_products.category_id'),
        ];
        $select_column = array_merge_recursive($select_column, $this->select_column);

        return SalesOrder::query()
            ->select($select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->searchCondition($conditions)
            ->groupBy(DB::raw("DATE_FORMAT(sales_orders.order_date, '%Y年%m月')"))
            ->oldest('m_products.category_id')
            ->oldest(DB::raw("DATE_FORMAT(sales_orders.order_date, '%Y年%m月')"));
    }

    /**
     * 伝票日付で絞り込んだsales_ordersのデータを取得
     *
     * @param array $conditions
     * @return Collection
     */
    public static function setSalesOrderFilterByOrderDate(array $conditions): Collection
    {
        return SalesOrder::query()
            ->with(['salesOrderDetail'])
            ->orderDate(
                $conditions['order_date']['start'] ?? null,
                $conditions['order_date']['end'] ?? null
            )
            ->get();
    }

    /**
     * sales_orderとsales_order_detailsをjoinしたクエリービルダー
     *
     * @param array $select_column
     * @return Builder
     */
    public static function setSalesOrder(array $select_column): Builder
    {
        return SalesOrder::query()
            ->salesOrderDetailSelectJoin($select_column);
    }

    /**
     * 検索結果を取得(年度合計)
     *
     * @param array $conditions
     * @return int
     */
    public static function getSomeTotal(array $conditions): int
    {
        return self::setSalesOrderFilterByOrderDate($conditions)
            ->sum(function ($details) use ($conditions) {
                return $details->getSumAnyColumnBySalesOrderDetail($conditions['aggregation_type']);
            });
    }

    /**
     * 検索結果を取得(年度合計)
     *
     * @param array $conditions
     * @return Collection
     */
    public static function getDataCategoryTotal(array $conditions): Collection
    {
        $select_column = [
            /** 年度合計 */
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ') AS month_total'),
            /** バイオノ有機 */
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ' * (CASE WHEN m_products.id = ' . 4 . ' THEN 1 ELSE 0 END)) AS Bio_total'),
            /** エキタン有機 */
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ' * (CASE WHEN m_products.id = ' . 3 . ' THEN 1 ELSE 0 END)) AS Equitan_total'),
            /** 肥料 */
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ' * (CASE WHEN m_products.category_id = ' . Categories::FERTILIZER . ' THEN 1 ELSE 0 END)) AS fertilizer_total'),
            /** ストリーム・タイフーン */
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ' * (CASE WHEN m_products.id IN (' . 144 . ',' . 146 . ',' . 234 . ') THEN 1 ELSE 0 END)) AS Stream_total'),
        ];

        return SalesOrder::query()
            ->select($select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->searchCondition($conditions)
            ->get();
    }

    /**
     * 検索結果を取得(種別)
     *
     * @param array $conditions
     * @param int|null $category_id
     * @return Collection
     */
    public static function getDataMonthTotal(array $conditions, ?int $category_id): Collection
    {
        $select_column = [
            /** カテゴリーID */
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ') AS year_total'),
        ];
        $array = [
            '04' => 'april_total',
            '05' => 'may_total',
            '06' => 'june_total',
            '07' => 'july_total',
            '08' => 'august_total',
            '09' => 'september_total',
            '10' => 'october_total',
            '11' => 'november_total',
            '12' => 'december_total',
            '01' => 'january_total',
            '02' => 'february_total',
            '03' => 'march_total',
        ];

        foreach ($array as $key => $value) {
            $select_column[] = DB::raw("SUM(sales_order_details.{$conditions['aggregation_type']} * (CASE WHEN DATE_FORMAT(sales_orders.order_date, '%m') = '" . $key . "' THEN 1 ELSE 0 END)) AS " . $value);
        }

        if (is_null($category_id)) {
            return self::setSalesOrder($select_column)
                ->searchCondition($conditions)
                ->get();
        }

        return self::setSalesOrder($select_column)
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->where('m_products.id', '=', $category_id)
            ->searchCondition($conditions)
            ->get();
    }

    /**
     * 検索結果を取得(ページネーション)
     *
     * @param array $conditions
     * @return LengthAwarePaginator
     */
    public function getSearchResultPaginate(array $conditions): LengthAwarePaginator
    {
        return $this->getDataCategory($conditions)
            ->groupBy('m_products.category_id')
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
        return $this->getDataCategory($conditions)
            ->groupBy('m_products.category_id')
            ->get();
    }

    /**
     * 検索結果を取得
     *
     * @param array $conditions
     * @return Collection
     */
    public function getProSearchResult(array $conditions): Collection
    {
        return $this->model->getDataPro($conditions)
            ->groupBy('m_products.category_id')
            ->get();
    }

    /**
     * 検索結果を取得(得意先別)
     *
     * @param array $conditions
     * @return Collection
     */
    public function getCustomerSearchResult(array $conditions): Collection
    {
        return $this->model->getDataCustomer($conditions)
            ->groupBy('m_customers.id')
            ->get();
    }

    /**
     * 検索結果を取得(バイオノ)
     *
     * @param array $conditions
     * @return Collection
     */
    public function getBioSearchResult(array $conditions): Collection
    {
        return $this->model->getDataBio($conditions)
            ->where('m_products.id', '=', '4')
            ->groupBy('m_customers.id')
            ->get();
    }

    /**
     * 検索結果を取得(エキタン)
     *
     * @param array $conditions
     * @return Collection
     */
    public function getEquitanSearchResult(array $conditions): Collection
    {
        return $this->model->getDataEquitan($conditions)
            ->where('m_products.id', '=', '3')
            ->groupBy('m_customers.id')
            ->get();
    }

    /**
     * 検索結果を取得(肥料)
     *
     * @param array $conditions
     * @return Collection
     */
    public function getFertilizerSearchResult(array $conditions): Collection
    {
        return $this->model->getDataFertilizer($conditions)
            ->where('m_products.category_id', '=', '1')
            ->groupBy('m_products.id')
            ->get();
    }

    /**
     * 検索結果を取得(ストリームライン・タイフーン)
     *
     * @param array $conditions
     * @return Collection
     */
    public function getStreamSearchResult(array $conditions): Collection
    {
        return $this->model->getDataStream($conditions)
            ->whereIn('m_products.id', [144, 146, 234])
            ->groupBy('m_customers.id')
            ->latest('year_total')
            ->get();
    }

    /**
     * m_categories テーブルとのリレーション
     *
     * @param int $order_detail
     * @return object
     */
    public function mCategory(int $order_detail): object
    {
        return MasterCategory::query()->find($order_detail);
    }

    /**
     * m_customers テーブルとのリレーション
     *
     * @param int $order_detail
     * @return object
     */
    public function mCustomer(int $order_detail): object
    {
        return MasterCustomer::query()->find($order_detail);
    }

    /**
     * m_products テーブルとのリレーション
     *
     * @param int $order_detail
     * @return object
     */
    public function mProduct(int $order_detail): object
    {
        return MasterProduct::query()->find($order_detail);
    }
}
