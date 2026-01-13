<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\System;

/**
 * 本社情報マスターテーブル用定数クラス
 */
class HeadOfficeInfoConst
{
    /** 自社ID */
    public const COMPANY_ID = 1;

    /** 会社名 最大桁数 */
    public const COMPANY_NAME_MAX_LENGTH = 100;

    /** 代表者名 最大桁数 */
    public const REPRESENTATIVE_NAME_MAX_LENGTH = 100;

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

    /** 社印画像 サイズ最大値 */
    public const COMPANY_SEAL_IMAGE_MAX_SIZE = 1024;

    /** 社印画像ファイル名 最大桁数 */
    public const COMPANY_SEAL_IMAGE_FILE_NAME_MAX_LENGTH = 50;

    /** 社印画像ファイル名 最大幅（リサイズ上限） */
    public const COMPANY_SEAL_IMAGE_FILE_NAME_MAX_WIDTH = 360;

    /** 社印画像ファイル名 最大高さ（リサイズ上限） */
    public const COMPANY_SEAL_IMAGE_FILE_NAME_MAX_HEIGHT = 100;

    /** インボイス番号 */
    public const INVOICE_NUMBER_MAX_LENGTH = 14;

    /** 振込先 最大桁数 */
    public const ACCOUNT_NUMBER_MAX_LENGTH = 50;
}
