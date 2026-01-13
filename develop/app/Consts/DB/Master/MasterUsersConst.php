<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Master;

/**
 * ユーザーマスターテーブル用定数クラス
 */
class MasterUsersConst
{
    /** ID 最大桁数（UNSIGNED INT） */
    public const ID_MAX_LENGTH = 10;

    /** コード 最大桁数 */
    public const CODE_MAX_LENGTH = 9;

    /** コード 最小値 */
    public const CODE_MIN_VALUE = 1;

    /** コード 最大値 */
    public const CODE_MAX_VALUE = 999999999;

    /** ログインID 最小桁数 */
    public const LOGIN_ID_MIN_LENGTH = 4;

    /** ログインID 最大桁数 */
    public const LOGIN_ID_MAX_LENGTH = 100;

    /** パスワード 最小桁数 */
    public const PASSWORD_MIN_LENGTH = 8;

    /** パスワード 最大桁数 */
    public const PASSWORD_MAX_LENGTH = 100;

    /** 名前 最大桁数 */
    public const NAME_MAX_LENGTH = 100;

    /** メールアドレス 最大桁数 */
    public const EMAIL_MAX_LENGTH = 255;

    /** メモ 最大桁数 */
    public const MEMO_MAX_LENGTH = 100;

    /** リメンバートークン 最大桁数 */
    public const REMEMBER_TOKEN_MAX_LENGTH = 100;
}
