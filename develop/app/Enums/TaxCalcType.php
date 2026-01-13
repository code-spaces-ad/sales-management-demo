<?php

/**
 * 税計算区分用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 税計算区分用Enum
 */
final class TaxCalcType extends BaseEnum
{
    /** 請求毎 */
    public const int BILLING = 1;

    /** 伝票毎 */
    public const int ORDER = 2;

    /** 明細毎 */
    public const int DETAIL = 3;

    /** 無処理 */
    public const int NONE = 4;

    /** 店頭販売 */
    public const int STORE_SALES = 0;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::BILLING => '請求毎',
            self::ORDER => '伝票毎',
            self::DETAIL => '明細毎',
            self::NONE => '無処理',
            self::STORE_SALES => '店頭販売',
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
            'billing' => self::BILLING,
            'order' => self::ORDER,
            'detail' => self::DETAIL,
            'none' => self::NONE,
            'store_sales' => self::STORE_SALES,
        ];
    }
}
