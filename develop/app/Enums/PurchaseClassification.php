<?php

/**
 * 仕入分類用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 仕入分類用Enum
 */
final class PurchaseClassification extends BaseEnum
{
    /** 仕入 */
    public const int CLASSIFICATION_PURCHASE = 0;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::CLASSIFICATION_PURCHASE => '仕入',
        ];
    }
}
