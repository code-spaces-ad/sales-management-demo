<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Master;

/**
 * 得意先マスターテーブル用定数クラス
 */
class MasterCustomersConst
{
    /** 得意先ID 最大桁数 */
    public const ID_MAX_LENGTH = 10;

    /** 得意先コード 最大桁数 */
    public const CODE_MAX_LENGTH = 8;

    /** 得意先名 最大桁数 */
    public const NAME_MAX_LENGTH = 100;

    /** 得意先名かな 最大桁数 */
    public const NAME_KANA_MAX_LENGTH = 150;

    /** 郵便番号1 */
    public const POSTAL_CODE1_MAX_LENGTH = 3;

    /** 郵便番号2 */
    public const POSTAL_CODE2_MAX_LENGTH = 4;

    /** 住所1 */
    public const ADDRESS1_MAX_LENGTH = 100;

    /** 住所2 */
    public const ADDRESS2_MAX_LENGTH = 100;

    /** 電話番号 */
    public const TEL_NUMBER_MAX_LENGTH = 20;

    /** FAX番号 */
    public const FAX_NUMBER_MAX_LENGTH = 20;

    /** メールアドレス */
    public const EMAIL_MAX_LENGTH = 255;

    /** 備考 */
    public const NOTE_MAX_LENGTH = 100;

    /** 得意先コード 最小値 */
    public const CODE_MIN_VALUE = 1;

    /** 得意先コード 最大値 */
    public const CODE_MAX_VALUE = 99999999;

    /** 請求締日 最小値 */
    public const CLOSING_DATE_MIN_VALUE = 0;

    /** 請求締日 最大値 */
    public const CLOSING_DATE_MAX_VALUE = 31;
}
