<?php

/**
 * POS送信（販売管理→POS）種別用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * POS送信（販売管理→POS）種別用Enum
 */
final class PosSendApiType extends BaseEnum
{
    /** 商品マスタ */
    public const int PRODUCT = 1;

    /** 得意先マスタ */
    public const int CUSTOMER = 2;

    /** 得意先別単価マスタ */
    public const int UNIT_PRICE_CUSTOMER = 3;

    /** 担当者マスタ */
    public const int EMPLOYEE = 4;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::PRODUCT => '商品マスタ',
            self::CUSTOMER => '得意先マスタ',
            self::UNIT_PRICE_CUSTOMER => '得意先別単価マスタ',
            self::EMPLOYEE => '担当者マスタ',
        ];
    }
}
