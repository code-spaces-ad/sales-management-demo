<?php

/**
 * 税区分用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 税区分用Enum
 */
final class TaxType extends BaseEnum
{
    /** 外税 */
    public const int OUT_TAX = 1;

    /** 内税 */
    public const int IN_TAX = 2;

    /** 非課税 */
    public const int TAX_EXEMPT = 3;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::OUT_TAX => '外税',
            self::IN_TAX => '内税',
            self::TAX_EXEMPT => '非課税',
        ];
    }

    /**
     * Javascript 引き渡し用
     *
     * @return array[]
     */
    public static function toJavascriptArray(): array
    {
        return [
            'out_tax' => self::OUT_TAX,
            'in_tax' => self::IN_TAX,
            'tax_exempt' => self::TAX_EXEMPT,
        ];
    }
}
