<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Enums\PosReceiveApiType;
use App\Enums\PosSendApiType;

/**
 * POS連携用ヘルパークラス
 */
class PosApiHelper
{
    /**
     * POS送信処理用のURLを取得
     *
     * @return array
     */
    public static function getPosSendApiUrl()
    {
        $url = [];

        $list = PosSendApiType::asSelectArray();
        foreach ($list as $key => $value) {
            if ($key === PosSendApiType::PRODUCT) {
                $url[$key] = route('pos_send.product');
            }
            if ($key === PosSendApiType::CUSTOMER) {
                $url[$key] = route('pos_send.customer');
            }
            if ($key === PosSendApiType::UNIT_PRICE_CUSTOMER) {
                $url[$key] = route('pos_send.unit_price_customer');
            }
            if ($key === PosSendApiType::EMPLOYEE) {
                $url[$key] = route('pos_send.employee');
            }
        }

        return $url;
    }

    /**
     * POS受信処理用のURLを取得
     *
     * @return array
     */
    public static function getPosReceiveApiUrl()
    {
        $url = [];

        $list = PosReceiveApiType::asSelectArray();
        foreach ($list as $key => $value) {
            if ($key === PosReceiveApiType::SALES) {
                $url[$key] = route('pos_receive.sales');
            }
            if ($key === PosReceiveApiType::PURCHASE) {
                $url[$key] = route('pos_receive.purchase');
            }
            if ($key === PosReceiveApiType::INVENTORY) {
                $url[$key] = route('pos_receive.inventory');
            }
            if ($key === PosReceiveApiType::SHIPMENT) {
                $url[$key] = route('pos_receive.shipment');
            }
        }

        return $url;
    }
}
