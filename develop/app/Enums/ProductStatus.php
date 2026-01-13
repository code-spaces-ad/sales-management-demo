<?php

/**
 * 預金種目用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 預金種目用Enum
 */
final class ProductStatus extends BaseEnum
{
    /** 完成品 */
    public const int FINISHED_PRODUCT = 1;

    /** 半製品 */
    public const int SEMI_FINISHED_PRODUCT = 2;

    /** 資材 */
    public const int MATERIALS = 3;

    /** 原料 */
    public const int RAW_MATERIALS = 4;

    /** 送料 */
    public const int SHIPPING = 5;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::FINISHED_PRODUCT => '完成品',
            self::SEMI_FINISHED_PRODUCT => '半製品',
            self::MATERIALS => '資材',
            self::RAW_MATERIALS => '原料',
            self::SHIPPING => '送料',
        ];
    }
}
