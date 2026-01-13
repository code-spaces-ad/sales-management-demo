<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Consts\DB\Master;

/**
 * 得意先別単価マスターテーブル用定数クラス
 */
class MasterCustomerPriceConst
{
    /** コード値 最大桁数 */
    public const CODE_MAX_LENGTH = 4;

    /** 納品先名かな 最大桁数 */
    public const NAME_KANA_MAX_LENGTH = 50;

    /** 名前 最大桁数 */
    public const NAME_MAX_LENGTH = 50;

    /**  通常税率_単価 最大桁数 */
    public const TAX_INCLUDED_MAX_LENGTH = 99999999.9999;

    /** 軽減税率_単価 最大桁数 */
    public const REDUCED_TAX_INCLUDED_MAX_LENGTH = 99999999.9999;

    /** 税抜単価 最大桁数 */
    public const UNIT_PRICE_MAX_LENGTH = 99999999.9999;

    /** 備考 最大桁数 */
    public const NOTE_MAX_LENGTH = 100;
}
