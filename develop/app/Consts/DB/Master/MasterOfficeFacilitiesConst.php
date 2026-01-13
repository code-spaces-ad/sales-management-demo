<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Master;

/**
 * 部門マスターテーブル用定数クラス
 */
class MasterOfficeFacilitiesConst
{
    /** コード 最大桁数 */
    public const CODE_MAX_LENGTH = 4;

    /** 名前 最大桁数 */
    public const NAME_MAX_LENGTH = 100;

    /** 備考 */
    public const NOTE_MAX_LENGTH = 100;

    /** コード 最小値 */
    public const CODE_MIN_VALUE = 1;

    /** コード 最大値 */
    public const CODE_MAX_VALUE = 9999;
}
