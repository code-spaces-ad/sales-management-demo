<?php

/**
 * 得意先_商品リレーションモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 仕入先_商品リレーションモデル
 */
class MasterSupplierProduct extends Model
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
    protected $table = 'm_suppliers_products';

    /**
     * @var string 主キーカラム
     */
    protected $primaryKey = 'supplier_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'supplier_id',
        'product_id',
        'unit_name',
        'last_unit_price',
    ];

    // region eloquent-relationships

    /**
     * 得意先テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mSupplier(): BelongsTo
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }

    /**
     * 敬称テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class, 'product_id');
    }

    // endregion eloquent-relationships
}
