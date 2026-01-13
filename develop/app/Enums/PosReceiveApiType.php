<?php

/**
 * POS受信（POS→販売管理）種別用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * POS受信（POS→販売管理）種別用Enum
 */
final class PosReceiveApiType extends BaseEnum
{
    /** 販売 */
    public const int SALES = 1;

    /** 仕入 */
    public const int PURCHASE = 2;

    /** 棚卸 */
    public const int INVENTORY = 3;

    /** 工場出荷 */
    public const int SHIPMENT = 4;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::SALES => '販売',
            self::PURCHASE => '仕入',
            self::INVENTORY => '棚卸',
            self::SHIPMENT => '工場出荷',
        ];
    }
}
