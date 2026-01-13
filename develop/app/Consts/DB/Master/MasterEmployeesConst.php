<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Master;

/**
 * 社員マスターテーブル用定数クラス
 */
class MasterEmployeesConst
{
    /** ID 最大桁数 */
    public const ID_MAX_LENGTH = 10;

    /** コード値 最大桁数 */
    public const CODE_MAX_LENGTH = 10;

    /** 名前 最大桁数 */
    public const NAME_MAX_LENGTH = 100;

    /** 名前かな 最大桁数 */
    public const NAME_KANA_MAX_LENGTH = 150;

    /** 備考 最大桁数 */
    public const NOTE_MAX_LENGTH = 150;

    /** コード 最小値 */
    public const CODE_MIN_VALUE = 1;

    /** コード 最大値 */
    public const CODE_MAX_VALUE = 9999999999;
}
