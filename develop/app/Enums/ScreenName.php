<?php

/**
 * 画面判別用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 画面判別用Enum
 */
final class ScreenName extends BaseEnum
{
    /** 受注伝票入力 */
    public const int ORDERS_RECEIVED = 1;

    /** 売上伝票入力 */
    public const int SALE_ORDERS = 2;

    /** 仕入伝票入力 */
    public const int PURCHASE_ORDERS = 3;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::ORDERS_RECEIVED => '受注伝票入力',
            self::SALE_ORDERS => '売上伝票入力',
            self::PURCHASE_ORDERS => '仕入伝票入力',
        ];
    }
}
