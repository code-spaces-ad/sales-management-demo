<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Master;

/**
 * 単位マスターテーブル用定数クラス
 */
class MasterUnitConst
{
    /** コード値 最大桁数（0000～9999） */
    public const CODE_MAX_LENGTH = 4;

    /** 名前 最大桁数 */
    public const NAME_MAX_LENGTH = 5;

    /**  単位固定 1：pcs */
    public const UNIT_ID_FIXED_VALUE = 1;
}
