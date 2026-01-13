<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Sale;

/**
 * 入金伝票詳細テーブル用定数クラス
 */
class DepositOrderDetailConst
{
    /** 各金額 最大桁数（0～999,999,999） */
    public const AMOUNT_MAX_LENGTH = 9;

    /** 各備考 最大桁数 */
    public const NOTE_MAX_LENGTH = 10;
}
