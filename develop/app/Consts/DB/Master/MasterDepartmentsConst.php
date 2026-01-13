<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Master;

/**
 * 部門マスターテーブル用定数クラス
 */
class MasterDepartmentsConst
{
    /** コード 最大桁数 */
    public const CODE_MAX_LENGTH = 4;

    /** 名前 最大桁数 */
    public const NAME_MAX_LENGTH = 100;

    /** 名前かな 最大桁数 */
    public const NAME_KANA_MAX_LENGTH = 150;

    /** 備考 */
    public const NOTE_MAX_LENGTH = 100;

    /** コード 最小値 */
    public const CODE_MIN_VALUE = 1;

    /** コード 最大値 */
    public const CODE_MAX_VALUE = 9999;

    /** 略称 最大桁数 */
    public const MNEMONIC_NAME_MAX_LENGTH = 50;
}
