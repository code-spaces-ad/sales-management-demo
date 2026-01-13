<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

/**
 * 文字列用ヘルパークラス
 */
class StringHelper
{
    /**
     * ID付きの名前を取得
     *
     * @param string $id ID
     * @param string $name 名前
     * @return string
     */
    public static function getNameWithId(string $id, string $name): string
    {
        // 表示フォーマット：「XXX: YYY」 ※IDはゼロ埋めなし
        return "{$id}: {$name}";
    }

    /**
     * 対象の文字列（配列）を空白に書き換える
     *
     * @param string $base
     * @param array $replace
     * @return string
     */
    public static function replaceToBlank(string $base, array $replace): string
    {
        return str_replace($replace, '', $base);
    }
}
