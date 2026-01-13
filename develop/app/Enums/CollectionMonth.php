<?php

/**
 * 回収月用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 回収月用Enum
 */
final class CollectionMonth extends BaseEnum
{
    /** 当月 */
    public const int THIS_MONTH = 1;

    /** 翌月 */
    public const int NEXT_MONTH = 2;

    /** 翌々月 */
    public const int TWO_MONTHS_LATER = 3;

    /** 3ヶ月後 */
    public const int THREE_MONTHS_LATER = 4;

    /** 4ヶ月後 */
    public const int FOUR_MONTHS_LATER = 5;

    /** 5ヶ月後 */
    public const int FIVE_MONTHS_LATER = 6;

    /** 6ヶ月後 */
    public const int SIX_MONTHS_LATER = 7;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::THIS_MONTH => '当月',
            self::NEXT_MONTH => '翌月',
            self::TWO_MONTHS_LATER => '翌々月',
            self::THREE_MONTHS_LATER => '3ヶ月後',
            self::FOUR_MONTHS_LATER => '4ヶ月後',
            self::FIVE_MONTHS_LATER => '5ヶ月後',
            self::SIX_MONTHS_LATER => '6ヶ月後',
        ];
    }
}
