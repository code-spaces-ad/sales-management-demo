<?php

/**
 * 在庫管理有無用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 在庫管理有無用Enum
 */
final class IsControlInventory extends BaseEnum
{
    /** 管理しない */
    public const int DO_NOT_CONTROL = 0;

    /** 管理する */
    public const int DO_CONTROL = 1;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::DO_NOT_CONTROL => '管理しない',
            self::DO_CONTROL => '管理する',
        ];
    }
}
