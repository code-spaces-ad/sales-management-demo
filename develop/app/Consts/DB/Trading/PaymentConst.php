<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Trading;

/**
 * 支払伝票テーブル用定数クラス
 */
class PaymentConst
{
    /** 伝票番号 最大桁数（0～99,999,999） */
    public const ORDER_NUMBER_MAX_LENGTH = 8;

    /** 備考 最大桁数 */
    public const NOTE_MAX_LENGTH = 20;

    /** 明細備考 最大桁数 */
    public const NOTE_DETAIL_MAX_LENGTH = 10;
}
