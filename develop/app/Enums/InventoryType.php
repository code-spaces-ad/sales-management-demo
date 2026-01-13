<?php

/**
 * 入出庫用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 入出庫用Enum
 */
final class InventoryType extends BaseEnum
{
    /** 在庫調整（倉庫：倉庫ID=65533） */
    public const int INVENTORY_ADJUST = 65533;

    /** 仕入（入庫：倉庫ID=65534） */
    public const int INVENTORY_IN = 65534;

    /** 納品（出庫：倉庫ID=65535） */
    public const int INVENTORY_OUT = 65535;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::INVENTORY_ADJUST => '在庫調整',
            self::INVENTORY_IN => '仕入',
            self::INVENTORY_OUT => '納品',
        ];
    }
}
