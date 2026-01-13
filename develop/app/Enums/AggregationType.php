<?php

/**
 * 集計種別用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 集計種別用Enum
 */
final class AggregationType extends BaseEnum
{
    /** 金額 */
    public const string AMOUNT = 'sub_total';

    /** 数量 */
    public const string QUANTITY = 'quantity';

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::AMOUNT => '金額',
            self::QUANTITY => '数量',
        ];
    }
}
