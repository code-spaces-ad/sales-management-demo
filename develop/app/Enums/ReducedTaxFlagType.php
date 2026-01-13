<?php

/**
 * 軽減税率フラグタイプ用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 軽減税率フラグタイプ用Enum
 */
final class ReducedTaxFlagType extends BaseEnum
{
    /** 軽減税率ではない */
    public const int NOT_REDUCED_TAX = 0;

    /** 軽減税率 */
    public const int REDUCED_TAX = 1;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::NOT_REDUCED_TAX => '通常税率',
            self::REDUCED_TAX => '軽減税率',
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
            'not_reduced' => self::NOT_REDUCED_TAX,
            'reduced' => self::REDUCED_TAX,
        ];
    }
}
