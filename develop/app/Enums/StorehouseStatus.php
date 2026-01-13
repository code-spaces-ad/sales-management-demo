<?php

/**
 * 倉庫用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 倉庫用Enum
 */
final class StorehouseStatus extends BaseEnum
{
    /** 入庫 */
    public const int ENTRY = 1;

    /** 出庫 */
    public const int ISSUE = 2;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::ENTRY => '入庫',
            self::ISSUE => '出庫',
        ];
    }
}
