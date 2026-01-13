<?php

/**
 * 取引種別マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 取引種別マスターモデル
 */
class MasterTransactionType extends Model
{
    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'm_transaction_types';

    /**
     * 「掛売」の id を返却
     *
     * @return mixed
     */
    public static function getCreditSalesId()
    {
        return self::query()
            ->where('name', '掛売')
            ->value('id');
    }
}
