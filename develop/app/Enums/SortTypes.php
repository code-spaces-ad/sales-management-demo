<?php

/**
 * ソート順用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * ソート順用Enum
 */
final class SortTypes extends BaseEnum
{
    /** 五十音順 */
    public const int SYLLABARY_ORDER = 1;

    /** カテゴリー順 */
    public const int CATEGORY = 2;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::SYLLABARY_ORDER => '五十音順',
            self::CATEGORY => 'カテゴリー順',
        ];
    }
}
