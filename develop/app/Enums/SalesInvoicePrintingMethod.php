<?php

/**
 * 請求書印刷方式用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 請求書印刷方式用Enum
 */
final class SalesInvoicePrintingMethod extends BaseEnum
{
    /** 横 */
    public const int HORIZONTAL = 1;

    /** 縦 */
    public const int VERTICAL = 2;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::HORIZONTAL => '横',
            self::VERTICAL => '縦',
        ];
    }
}
