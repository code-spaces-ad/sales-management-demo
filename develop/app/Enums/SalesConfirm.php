<?php

/**
 * 売上確定用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 売上確定用Enum
 */
final class SalesConfirm extends BaseEnum
{
    /** 未確定 */
    public const int UNSETTLED = 0;

    /** 確定 */
    public const int CONFIRM = 1;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::UNSETTLED => '未確定',
            self::CONFIRM => '確定',
        ];
    }
}
