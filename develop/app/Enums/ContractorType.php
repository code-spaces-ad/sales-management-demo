<?php

/**
 * 請負業者タイプ用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 請負業者タイプ用Enum
 */
final class ContractorType extends BaseEnum
{
    /** 元請 */
    public const int MAIN_CONTRACT = 1;

    /** 下請 */
    public const int SUB_CONTRACT = 2;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::MAIN_CONTRACT => '元請',
            self::SUB_CONTRACT => '下請',
        ];
    }
}
