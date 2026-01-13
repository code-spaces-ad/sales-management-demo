<?php

/**
 * ベース用Enum
 */

namespace App\Base;

use BenSampo\Enum\Enum;

/**
 * ベース用Enum
 */
class BaseEnum extends Enum
{
    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [];
    }

    /**
     * 翻訳 getDescription() をオーバーライド
     */
    public static function getDescription(mixed $value): string
    {
        return static::mappings()[$value] ?? 'nothing';
    }

    /**
     * 翻訳から値取得 getValue() をオーバーライド
     *
     * @param mixed $key
     * @return int|null
     */
    public static function getValue(string $key): ?int
    {
        return array_search($key, static::mappings(), true) ?: null;
    }
}
