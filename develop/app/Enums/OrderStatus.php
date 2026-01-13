<?php

/**
 * 伝票状態用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 伝票状態用Enum
 */
final class OrderStatus extends BaseEnum
{
    /** 見積済 */
    public const int ESTIMATED = 1;

    /** 注文済 */
    public const int ORDERED = 2;

    /** 納品済 */
    public const int DELIVERED = 3;

    /** 請求済 */
    public const int BILLED = 4;

    /** 支払済 */
    public const int PAID = 5;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::ESTIMATED => '見積済',
            self::ORDERED => '注文済',
            self::DELIVERED => '納品済',
            self::BILLED => '請求済',
            self::PAID => '支払済',
        ];
    }
}
