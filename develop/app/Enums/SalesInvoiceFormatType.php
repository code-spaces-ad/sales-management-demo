<?php

/**
 * 請求書書式用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 請求書書式用Enum
 */
final class SalesInvoiceFormatType extends BaseEnum
{
    /** 現場別鏡有り */
    public const int MIRROR = 1;

    /** 現場別鏡無し */
    public const int NO_MIRROR = 2;

    /** 得意先別 */
    public const int CUSTOMER = 3;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::MIRROR => '現場別鏡有り',
            self::NO_MIRROR => '現場別鏡無し',
            self::CUSTOMER => '得意先別',
        ];
    }
}
