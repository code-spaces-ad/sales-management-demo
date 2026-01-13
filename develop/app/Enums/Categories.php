<?php

/**
 * カテゴリー用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * カテゴリー用Enum
 */
final class Categories extends BaseEnum
{
    /** 肥料 */
    public const int FERTILIZER = 1;

    /** 農薬 */
    public const int PESTICIDE = 2;

    /** 資材 */
    public const int MATERIAL = 3;

    /** 種子 */
    public const int SEED = 4;

    /** その他 */
    public const int ANOTHER = 10;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::FERTILIZER => '肥料',
            self::PESTICIDE => '農薬',
            self::MATERIAL => '資材',
            self::SEED => '種子',
            self::ANOTHER => 'その他',
        ];
    }
}
