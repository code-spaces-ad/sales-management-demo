<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Master;

/**
 * 商品マスターテーブル用定数クラス
 */
class MasterProductsConst
{
    /** 商品ID 最大桁数 */
    public const ID_MAX_LENGTH = 10;

    /** 商品コード 最大桁数 */
    public const CODE_MAX_LENGTH = 8;

    /** 商品名 最大桁数 */
    public const NAME_MAX_LENGTH = 100;

    /** 商品名かな 最大桁数 */
    public const NAME_KANA_MAX_LENGTH = 150;

    /** 商品コード 最小値 */
    public const CODE_MIN_VALUE = 1;

    /** 商品コード 最大値 */
    public const CODE_MAX_VALUE = 99999999;

    /** 単価小数桁数 最小値 */
    public const UNIT_PRICE_DECIMAL_DIGIT_MIN_VALUE = 0;

    /** 単価小数桁数 最大値 */
    public const UNIT_PRICE_DECIMAL_DIGIT_MAX_VALUE = 4;

    /** 数量小数桁数 最小値 */
    public const QUANTITY_DECIMAL_DIGIT_MIN_VALUE = 0;

    /** 数量小数桁数 最大値 */
    public const QUANTITY_DECIMAL_DIGIT_MAX_VALUE = 4;

    /** JANコード 最大桁数 */
    public const JAN_CODE_MAX_LENGTH = 20;

    /** 相手先商品コード 最大桁数 */
    public const CUSTOMER_PRODUCT_CODE_MAX_LENGTH = 50;

    /** 備考 最大桁数 */
    public const NOTE_MAX_LENGTH = 100;

    /** 仕様 最大桁数 */
    public const SPECIFICATION_MAX_LENGTH = 100;
}
