<?php

/**
 * PosApi 共通定義
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\API;

/**
 * 在庫テーブル用定数クラス
 */
class PosApiConst
{
    /** POS連携先サーバ */
    public const string POS_URL_PROD = 'https://wakohen2025.ec-regi.net';   // todo 未確定、要確認!!!

    public const string POS_URL_ST = 'https://wakohen2025.ec-regi.net/dev-wakohen21-data';

    /** 販売データ受信時の送信先エンドポイント */
    public const string RECEIVE_SALES = '/class/sync_pos/service/api_service/api_sales.php';

    public const string RECEIVE_SALES_URL_PROD = self::POS_URL_PROD . self::RECEIVE_SALES;

    public const string RECEIVE_SALES_URL_ST = self::POS_URL_ST . self::RECEIVE_SALES;

    /** 棚卸データ受信時の送信先エンドポイント */
    public const string RECEIVE_INVENTORY = '/class/sync_pos/service/api_service/api_inventory.php';

    public const string RECEIVE_INVENTORY_URL_PROD = self::POS_URL_PROD . self::RECEIVE_INVENTORY;

    public const string RECEIVE_INVENTORY_URL_ST = self::POS_URL_ST . self::RECEIVE_INVENTORY;

    /** 工場出庫データ時の送信先エンドポイント */
    public const string RECEIVE_FACTORY_SHIPPING = '/class/sync_pos/service/api_service/api_shipment.php';

    public const string RECEIVE_FACTORY_SHIPPING_URL_PROD = self::POS_URL_PROD . self::RECEIVE_FACTORY_SHIPPING;

    public const string RECEIVE_FACTORY_SHIPPING_URL_ST = self::POS_URL_ST . self::RECEIVE_FACTORY_SHIPPING;

    /** 仕入データ時の送信先エンドポイント */
    public const string RECEIVE_PURCHASE = '/class/sync_pos/service/api_service/api_purchase.php';

    public const string RECEIVE_PURCHASE_URL_PROD = self::POS_URL_PROD . self::RECEIVE_PURCHASE;

    public const string RECEIVE_PURCHASE_URL_ST = self::POS_URL_ST . self::RECEIVE_PURCHASE;

    /** 棚卸データ送信時の受信先エンドポイント */
    public const string SEND_INVENTORY = '/class/sync_pos/service/api_service/rec_api_inventory_result.php';

    public const string SEND_INVENTORY_URL_PROD = self::POS_URL_PROD . self::SEND_INVENTORY;

    public const string SEND_INVENTORY_URL_ST = self::POS_URL_ST . self::SEND_INVENTORY;

    /**
     * データ格納時の定数
     */
    // POS連携データ
    public const POS_DATA = 1;

    // 販売管理の登録データ
    public const NOT_POS_DATA = 0;

    // POS連携データ 登録者ID
    public const POS_DATA_CREATER = 65534;

    // POS連携データ 店舗売上時の得意先コード
    public const POS_CUSTOMER_GENERAL = 100000;

    // POS連携データ 自社内売上時の得意先コード
    public const POS_CUSTOMER_WAKOHEN = 200000;

    // POS連携データ 店舗仕入時の仕入先コード
    public const POS_SUPPLIER_GENERAL = 100001;

    // POS連携 1回の受信伝票の件数を制限
    public const POS_RECEIVE_LIMIT_COUNT = 100;

    // POS連携 1回の送信の件数を制限
    public const POS_SEND_LIMIT_COUNT = 10000;

    // POS連携 棚卸ステータス：棚卸完了(要求可能)
    public const POS_RECEIVE_INVENTORY_STATUS_REQUESTABLE = 2;

    // POS連携 棚卸ステータス：棚卸 連携完了
    public const POS_RECEIVE_INVENTORY_STATUS_IMPORT_COMPLETED = 4;
}
