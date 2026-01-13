<?php

/**
 * LinkPOS用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * LinkPOS用Enum
 */
final class LinkPos extends BaseEnum
{
    /** 販売管理 */
    public const int SALES_MANAGEMENT = 0;

    /** POS */
    public const int POS = 1;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::SALES_MANAGEMENT => '販売管理',
            self::POS => 'POS',
        ];
    }
}
