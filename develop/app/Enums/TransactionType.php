<?php

/**
 * 取引種別用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 取引種別用Enum
 */
final class TransactionType extends BaseEnum
{
    /** 現 */
    public const int WITH_CASH = 1;

    /** 掛 */
    public const int ON_ACCOUNT = 2;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::WITH_CASH => '現',
            self::ON_ACCOUNT => '掛',
        ];
    }
}
