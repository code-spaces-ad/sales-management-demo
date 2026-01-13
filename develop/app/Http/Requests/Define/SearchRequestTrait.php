<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Define;

/**
 * 一覧(検索項目)用 トレイト
 */
trait SearchRequestTrait
{
    /**
     * バリデーションルールセット
     *
     * @param string $order_date
     * @return array
     */
    public static function setRulesArray(string $order_date = 'order_date'): array
    {
        return [
            'order_number' => ['bail', 'nullable'],
            $order_date => ['bail', 'nullable', 'array'],
            $order_date . '.start' => ['bail', 'nullable', 'date'],
            $order_date . '.end' => ['bail', 'nullable', 'date', 'after_or_equal:' . $order_date . '.start'],
            'order_month' => ['bail', 'nullable', 'date'],
        ];
    }

    /**
     * バリデーションアトリビュートセット
     *
     * @param string $order
     * @param string $order_date
     * @return array
     */
    public static function setAttributesArray(string $order = '伝票', string $order_date = 'order_date'): array
    {
        return [
            'order_number' => $order . '番号',
            $order_date => $order . '日付',
            $order_date . '.start' => $order . '日付（開始日）',
            $order_date . '.end' => $order . '日付（終了日）',
            'order_month' => $order . '月',
        ];
    }
}
