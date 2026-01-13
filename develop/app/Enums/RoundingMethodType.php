<?php

/**
 * 端数処理方法タイプ用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 端数処理方法タイプ用Enum
 */
final class RoundingMethodType extends BaseEnum
{
    /** 切り捨て */
    public const int ROUND_DOWN = 1;

    /** 切り上げ */
    public const int ROUND_UP = 2;

    /** 四捨五入 */
    public const int ROUND_OFF = 3;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::ROUND_DOWN => '切り捨て',
            self::ROUND_UP => '切り上げ',
            self::ROUND_OFF => '四捨五入',
        ];
    }

    /**
     * Javascript 引き渡し用
     *
     * @return array[]
     */
    public static function toJavascriptArray(): array
    {
        return [
            'round_down' => self::ROUND_DOWN,
            'round_up' => self::ROUND_UP,
            'round_off' => self::ROUND_OFF,
        ];
    }
}
