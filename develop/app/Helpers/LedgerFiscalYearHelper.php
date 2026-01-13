<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * 年度別販売実績表用ヘルパークラス
 */
class LedgerFiscalYearHelper
{
    /**
     * 種別累計売上のカラムを取得
     *
     * @param array $select_column
     * @return array
     */
    public static function getCategoryColumn(array $select_column): array
    {
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
            $select_column[] = DB::raw('MAX(fiscal_year_total.' . $value . ') AS ' . $value);
        }

        return $select_column;
    }

    /**
     * 種別累計売上のカラムを取得
     *
     * @param array $fiscal_year_column
     * @param array $search_condition_input_data
     * @return array
     */
    public static function getCategoryFiscalYearColumn(array $fiscal_year_column, array $conditions): array
    {
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
            $fiscal_year_column[] = DB::raw("SUM(sales_order_details.{$conditions['aggregation_type']} * (CASE WHEN DATE_FORMAT(sales_orders.order_date, '%m') = '" . $key . "' THEN 1 ELSE 0 END)) AS " . $value);
        }

        return $fiscal_year_column;
    }

    /**
     * 月から名称を取得する
     *
     * @param $month
     * @return string
     */
    public static function getNameByMonth($month)
    {
        $array = [
            '1' => 'january_total',
            '2' => 'february_total',
            '3' => 'march_total',
            '4' => 'april_total',
            '5' => 'may_total',
            '6' => 'june_total',
            '7' => 'july_total',
            '8' => 'august_total',
            '9' => 'september_total',
            '10' => 'october_total',
            '11' => 'november_total',
            '12' => 'december_total',
        ];

        return $array[$month];
    }

    /**
     * 名称から月を取得する
     *
     * @param $name
     * @return string
     */
    public static function getMonthByName($name)
    {
        $array = [
            'january_total' => '1',
            'february_total' => '2',
            'march_total' => '3',
            'april_total' => '4',
            'may_total' => '5',
            'june_total' => '6',
            'july_total' => '7',
            'august_total' => '8',
            'september_total' => '9',
            'october_total' => '10',
            'november_total' => '11',
            'december_total' => '12',
        ];

        return $array[$name];
    }
}
