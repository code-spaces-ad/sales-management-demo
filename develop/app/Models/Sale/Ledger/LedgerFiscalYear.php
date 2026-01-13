<?php

namespace App\Models\Sale\Ledger;

use App\Helpers\LedgerFiscalYearHelper;
use App\Models\Sale\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 年度別販売実績表モデル
 */
class LedgerFiscalYear extends Model
{
    /**
     * LedgerFiscalYear constructor.
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 検索結果を取得(種別)
     *
     * @param array $conditions
     * @return Builder
     */
    public function getDataPro(array $conditions): Builder
    {
        $select_column = [
            /** カテゴリーID */
            DB::raw('m_products.category_id'),
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ') AS year_total'),
        ];

        $fiscal_year_column = [
            DB::raw('m_products.category_id'),
        ];

        $select_column = LedgerFiscalYearHelper::getCategoryColumn($select_column);
        $fiscal_year_column = LedgerFiscalYearHelper::getCategoryFiscalYearColumn($fiscal_year_column, $conditions);

        $fiscal_year_total = (SalesOrder::query()
            ->select($fiscal_year_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->searchCondition($conditions)
            ->groupBy(DB::raw('m_products.category_id')));

        return SalesOrder::query()
            ->select($select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoinSub($fiscal_year_total, 'fiscal_year_total', 'm_products.category_id', '=', 'fiscal_year_total.category_id')
            ->searchCondition($conditions);
    }

    /**
     * 検索結果を取得(得意先別)
     *
     * @param array $conditions
     * @return Builder
     */
    public function getDataCustomer(array $conditions): Builder
    {
        $select_column = [
            /** 得意先ID */
            DB::raw('m_customers.id'),
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ') AS year_total'),
        ];

        $fiscal_year_column = [
            /** 得意先ID */
            DB::raw('m_customers.id'),
        ];

        $select_column = LedgerFiscalYearHelper::getCategoryColumn($select_column);
        $fiscal_year_column = LedgerFiscalYearHelper::getCategoryFiscalYearColumn($fiscal_year_column, $conditions);

        $fiscal_year_total = (SalesOrder::query()
            ->select($fiscal_year_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_customers', 'sales_orders.customer_id', '=', 'm_customers.id')
            ->searchCondition($conditions)
            ->groupBy(DB::raw('m_customers.id'))
            ->oldest('code'));

        return SalesOrder::query()
            ->select($select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_customers', 'sales_orders.customer_id', '=', 'm_customers.id')
            ->leftJoinSub($fiscal_year_total, 'fiscal_year_total', 'm_customers.id', '=', 'fiscal_year_total.id')
            ->searchCondition($conditions)
            ->latest('year_total');
    }

    /**
     * 検索結果を取得(バイオノ)
     *
     * @param array $conditions
     * @return Builder
     */
    public function getDataBio(array $conditions): Builder
    {
        $select_column = [
            /** 商品ID */
            DB::raw('m_products.id'),
            /** 得意先ID */
            DB::raw('m_customers.id AS customer_id'),
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ') AS year_total'),
        ];

        $fiscal_year_column = [
            /** 得意先ID */
            DB::raw('m_customers.id'),
        ];

        $select_column = LedgerFiscalYearHelper::getCategoryColumn($select_column);
        $fiscal_year_column = LedgerFiscalYearHelper::getCategoryFiscalYearColumn($fiscal_year_column, $conditions);

        $fiscal_year_total = (SalesOrder::query()
            ->select($fiscal_year_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_customers', 'sales_orders.customer_id', '=', 'm_customers.id')
            ->searchCondition($conditions)
            ->where('m_products.id', '=', '4')
            ->groupBy(DB::raw('m_customers.id')));

        return SalesOrder::query()
            ->select($select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_customers', 'sales_orders.customer_id', '=', 'm_customers.id')
            ->leftJoinSub($fiscal_year_total, 'fiscal_year_total', 'm_customers.id', '=', 'fiscal_year_total.id')
            ->searchCondition($conditions)
            ->groupBy('m_products.id')
            ->latest('year_total');
    }

    /**
     * 検索結果を取得(エキタン)
     *
     * @param array $conditions
     * @return Builder
     */
    public function getDataEquitan(array $conditions): Builder
    {
        $select_column = [
            /** 商品ID */
            DB::raw('m_products.id'),
            /** 得意先ID */
            DB::raw('m_customers.id AS customer_id'),
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ') AS year_total'),
        ];

        $fiscal_year_column = [
            /** 得意先ID */
            DB::raw('m_customers.id'),
        ];

        $select_column = LedgerFiscalYearHelper::getCategoryColumn($select_column);
        $fiscal_year_column = LedgerFiscalYearHelper::getCategoryFiscalYearColumn($fiscal_year_column, $conditions);

        $fiscal_year_total = (SalesOrder::query()
            ->select($fiscal_year_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_customers', 'sales_orders.customer_id', '=', 'm_customers.id')
            ->searchCondition($conditions)
            ->where('m_products.id', '=', '3')
            ->groupBy(DB::raw('m_customers.id')));

        return SalesOrder::query()
            ->select($select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_customers', 'sales_orders.customer_id', '=', 'm_customers.id')
            ->leftJoinSub($fiscal_year_total, 'fiscal_year_total', 'm_customers.id', '=', 'fiscal_year_total.id')
            ->searchCondition($conditions)
            ->groupBy('m_products.id')
            ->latest('year_total');
    }

    /**
     * 検索結果を取得(肥料別)
     *
     * @param array $conditions
     * @return Builder
     */
    public function getDataFertilizer(array $conditions): Builder
    {
        $select_column = [
            /** カテゴリーID */
            DB::raw('m_products.category_id'),
            /** 商品名 */
            DB::raw('m_products.id'),
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ') AS year_total'),
        ];

        $fiscal_year_column = [
            /** 商品名 */
            DB::raw('m_products.id'),
        ];

        $select_column = LedgerFiscalYearHelper::getCategoryColumn($select_column);
        $fiscal_year_column = LedgerFiscalYearHelper::getCategoryFiscalYearColumn($fiscal_year_column, $conditions);

        $fiscal_year_total = (SalesOrder::query()
            ->select($fiscal_year_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->searchCondition($conditions)
            ->where('m_products.category_id', '=', '1')
            ->groupBy(DB::raw('m_products.id')));

        return SalesOrder::query()
            ->select($select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoinSub($fiscal_year_total, 'fiscal_year_total', 'm_products.id', '=', 'fiscal_year_total.id')
            ->searchCondition($conditions)
            ->groupBy('m_products.category_id')
            ->latest('year_total');
    }

    /**
     * 検索結果を取得(ストリームライン・タイフーン)
     *
     * @param array $conditions
     * @return Builder
     */
    public function getDataStream(array $conditions): Builder
    {
        $select_column = [
            /** 商品名 */
            DB::raw('m_products.id'),
            /** 得意先ID */
            DB::raw('m_customers.id AS customer_id'),
            DB::raw('SUM(sales_order_details.' . $conditions['aggregation_type'] . ') AS year_total'),
        ];

        return SalesOrder::query()
            ->select($select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            })
            ->leftJoin('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->leftJoin('m_customers', 'sales_orders.customer_id', '=', 'm_customers.id')
            ->searchCondition($conditions)
            ->groupBy('m_products.id')
            ->oldest('m_products.id');
    }
}
