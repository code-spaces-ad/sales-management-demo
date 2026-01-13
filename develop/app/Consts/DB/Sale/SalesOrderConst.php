<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Sale;

/**
 * 売上伝票テーブル用定数クラス
 */
class SalesOrderConst
{
    /** 伝票番号 最大桁数（0～99,999,999） */
    public const ORDER_NUMBER_MAX_LENGTH = 8;

    /** 備考 最大桁数 */
    public const NOTE_MAX_LENGTH = 100;
}
