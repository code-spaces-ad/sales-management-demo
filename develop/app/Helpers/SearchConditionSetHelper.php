<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

/**
 * 検索条件セット用ヘルパー
 */
class SearchConditionSetHelper
{
    /**
     * 検索条件(query+defaults)セット
     *
     * @param array|null $query
     * @param array $defaults
     * @return array
     */
    public static function setSearchConditionInput(?array $query, array $defaults): array
    {
        // 検索条件が多重配列(order_dateのstart,end等)である場合の対応
        foreach ($defaults as $key => $value) {
            if (!is_array($value)) {
                continue;
            }
            // ページネーションのリンクをクリックした場合の対応
            if (!isset($query[$key]) && isset($query['page'])) {
                $query += [$key => array_fill_keys(array_keys($value), null)];

                continue;
            }
            if (!isset($query[$key])) {
                continue;
            }
            // $defaultsに有り、$queryに無いキーのみセット(値はnull)
            $query[$key] += array_fill_keys(array_diff(array_keys($value), array_keys($query[$key])), null);
        }

        return $query + $defaults;
    }
}
