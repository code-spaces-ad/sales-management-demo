<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Trading;

/**
 * 発注伝票テーブル用定数クラス
 */
class PurchaseOrderConst
{
    /** 発注番号 最大桁数（0～99,999,999） */
    public const ORDER_NUMBER_MAX_LENGTH = 8;

    /** 見積有効期限 最大桁数 */
    public const ESTIMATE_VALIDITY_PERIOD_MAX_LENGTH = 30;

    /** 備考 最大桁数 */
    public const NOTE_MAX_LENGTH = 100;
}
