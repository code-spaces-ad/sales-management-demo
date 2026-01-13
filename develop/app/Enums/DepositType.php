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
final class DepositType extends BaseEnum
{
    /** 普通預金口座 */
    public const int SAVINGS_ACCOUNT = 1;

    /** 当座預金口座 */
    public const int CHECKING_ACCOUNT = 2;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::SAVINGS_ACCOUNT => '普通',
            self::CHECKING_ACCOUNT => '当座',
        ];
    }
}
