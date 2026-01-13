<?php

/**
 * 売上分類用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * 売上分類用Enum
 */
final class SalesClassification extends BaseEnum
{
    /** 販売 */
    public const int CLASSIFICATION_SALE = 0;

    /** 返品 */
    public const int CLASSIFICATION_RETURN = 1;

    /** 取消 */
    public const int CLASSIFICATION_CANCEL = 99;

    /** サービス */
    public const int CLASSIFICATION_SERVICE = 3;

    /** 試飲 */
    public const int CLASSIFICATION_TASTING = 4;

    /** その他 */
    public const int CLASSIFICATION_OTHER = 5;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::CLASSIFICATION_SALE => '販売',
            self::CLASSIFICATION_RETURN => '返品',
            self::CLASSIFICATION_CANCEL => '取消',

            self::CLASSIFICATION_SERVICE => 'サービス',
            self::CLASSIFICATION_TASTING => '試飲',
            self::CLASSIFICATION_OTHER => 'その他',
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
            'classification_sale' => self::CLASSIFICATION_SALE,
            'classification_return' => self::CLASSIFICATION_RETURN,
            'classification_cancel' => self::CLASSIFICATION_CANCEL,
            'classification_service' => self::CLASSIFICATION_SERVICE,
            'classification_tasting' => self::CLASSIFICATION_TASTING,
            'classification_other' => self::CLASSIFICATION_OTHER,
        ];
    }
}
