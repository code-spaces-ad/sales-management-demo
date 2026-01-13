<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Master;

/**
 * 倉庫マスターテーブル用定数クラス
 */
class MasterWarehousesConst
{
    /** ID 最大桁数 */
    public const ID_MAX_LENGTH = 5;

    /** コード値 最大桁数 */
    public const CODE_MAX_LENGTH = 4;

    /** 名前 最大桁数 */
    public const NAME_MAX_LENGTH = 50;

    /** 名前かな 最大桁数 */
    public const NAME_KANA_MAX_LENGTH = 100;

    /** 倉庫コード 最小値 */
    public const CODE_MIN_VALUE = 1;

    /** 倉庫コード 最大値 */
    public const CODE_MAX_VALUE = 9999;
}
