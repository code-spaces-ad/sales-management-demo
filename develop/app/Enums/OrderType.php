<?php

/**
 * 伝票種別用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 伝票種別用Enum
 */
final class OrderType extends BaseEnum
{
    /** 現売 */
    public const int CASH_SALES = 1;

    /** 掛売 */
    public const int ACCOUNTS_RECEIVABLE = 2;

    /** 入金 */
    public const int DEPOSIT = 3;

    /** 売上 */
    public const int SALES = 4;

    /** 仕入 */
    public const int PURCHASE = 5;

    /** 支払 */
    public const int PAYMENT = 6;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::CASH_SALES => '現売',
            self::ACCOUNTS_RECEIVABLE => '掛売',
            self::DEPOSIT => '入金',
            self::SALES => '売上',
            self::PURCHASE => '仕入',
            self::PAYMENT => '支払',
        ];
    }
}
