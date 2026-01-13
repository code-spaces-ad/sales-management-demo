<?php

/**
 * 支払方法用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 支払方法用Enum
 */
final class PaymentMethodType extends BaseEnum
{
    /** 現金 */
    public const int CASH = 1;

    /** 小切手 */
    public const int CHECK = 2;

    /** 振込 */
    public const int TRANSFER = 3;

    /** 手形 */
    public const int BILL = 4;

    /** 相殺 */
    public const int OFFSET = 5;

    /** 値引 */
    public const int DISCOUNT = 6;

    /** 手数料 */
    public const int FEE = 7;

    /** その他 */
    public const int OTHER = 8;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::CASH => '現金',
            self::CHECK => '小切手',
            self::TRANSFER => '振込',
            self::BILL => '手形',
            self::OFFSET => '相殺',
            self::DISCOUNT => '値引',
            self::FEE => '手数料',
            self::OTHER => 'その他',
        ];
    }

    /**
     * 支払方法の種別を取得
     *
     * @param int $status
     * @return string
     */
    public static function getPaymentType(int $status): string
    {
        if ($status === PaymentMethodType::CASH) {
            return 'cash';
        }
        if ($status === PaymentMethodType::CHECK) {
            return 'check';
        }
        if ($status === PaymentMethodType::TRANSFER) {
            return 'transfer';
        }
        if ($status === PaymentMethodType::BILL) {
            return 'bill';
        }
        if ($status === PaymentMethodType::OFFSET) {
            return 'offset';
        }
        if ($status === PaymentMethodType::DISCOUNT) {
            return 'discount';
        }
        if ($status === PaymentMethodType::FEE) {
            return 'fee';
        }
        if ($status === PaymentMethodType::OTHER) {
            return 'other';
        }

        return 'other';
    }
}
