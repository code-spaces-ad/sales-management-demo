<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use Closure;
use Illuminate\Support\Collection;

/**
 * コレクションヘルパークラス
 */
class CollectionHelper
{
    /**
     * Collectionからカラム指定でデータ取得する
     *
     * @param Collection $data
     * @param string|array $properties
     * @param callable|null $formatter
     * @return Closure
     */
    public static function getData(Collection $data, string|array $properties, ?callable $formatter = null): Closure
    {
        return function ($item) use ($properties, $formatter) {
            // プロパティの取得（nullセーフ）
            $values = is_array($properties)
                ? array_map(fn ($prop) => $item->$prop ?? null, $properties)
                : [$item->$properties ?? null];

            // 整形関数があるなら使う、なければ単一 or スペース区切りで返す
            return $formatter
                ? $formatter(...$values)
                : (count($values) === 1 ? ($values[0] ?? '') : implode(' ', array_filter($values, fn ($v) => !is_null($v))));
        };
    }
}
