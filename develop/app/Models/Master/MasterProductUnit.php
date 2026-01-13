<?php

/**
 * 商品_単位リレーションモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 商品_単位リレーションモデル
 */
class MasterProductUnit extends Model
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
    protected $table = 'm_products_units';

    /**
     * @var string 主キーカラム
     */
    protected $primaryKey = 'product_id';

    // region eloquent-relationships

    /**
     * 単位 リレーション情報を取得
     *
     * @return HasOne
     */
    public function mProduct(): HasOne
    {
        return $this->hasOne(MasterProduct::class, 'id', 'product_id');
    }

    /**
     * 単位 リレーション情報を取得
     *
     * @return HasOne
     */
    public function mUnit(): HasOne
    {
        return $this->hasOne(MasterUnit::class, 'id', 'unit_id');
    }

    // endregion eloquent-relationships
}
