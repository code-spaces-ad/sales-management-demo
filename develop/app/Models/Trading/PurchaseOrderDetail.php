<?php

/**
 * 発注伝票詳細モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Trading;

use App\Models\Master\MasterProduct;
use App\Models\Master\MasterRoundingMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 売上伝票詳細モデル
 */
class PurchaseOrderDetail extends Model
{
    /**
     * factory
     */
    use HasFactory;

    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    /**
     * @var bool オートインインクリメント
     */
    public $incrementing = false;

    /**
     * @var string 主キーカラム
     */
    protected $primaryKey = 'purchase_order_id';

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_name',
        'unit_price',
        'discount',
        'sub_total',
        'sub_total_tax',
        'tax_type_id',
        'consumption_tax_rate',
        'reduced_tax_flag',
        'rounding_method_id',
        'note',
        'sort',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // region eloquent-relationships

    /**
     * 発注伝票 テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * m_rounding_methods テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mRoundingMethod(): BelongsTo
    {
        return $this->belongsTo(MasterRoundingMethod::class, 'rounding_method_id');
    }

    /**
     * m_products テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class, 'product_id');
    }

    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 消費税端数処理方法名を取得
     *
     * @return string
     */
    public function getRoundingMethodNameAttribute(): string
    {
        return $this->mRoundingMethod->name ?? '';
    }

    /**
     * 数量（小数桁数をカット）を取得
     *
     * @return string
     */
    public function getQuantityDigitCutAttribute()
    {
        $quantity_decimal_digit = $this->mProduct->quantity_decimal_digit ?? 0;
        if ($quantity_decimal_digit > 0) {
            $digit_cut = (4 - $quantity_decimal_digit) * -1;
        } else {
            // ※小数点も含めて削除
            $digit_cut = -5;
        }

        return substr($this->quantity, 0, $digit_cut);
    }

    /**
     * 単価（小数桁数をカット）を取得
     *
     * @return string
     */
    public function getUnitPriceDigitCutAttribute(): string
    {
        $unit_price_decimal_digit = $this->mProduct->unit_price_decimal_digit ?? 0;
        if ($unit_price_decimal_digit > 0) {
            $digit_cut = (4 - $unit_price_decimal_digit) * -1;
        } else {
            // ※小数点も含めて削除
            $digit_cut = -5;
        }

        return substr($this->unit_price, 0, $digit_cut);
    }

    // endregion eloquent-accessors
}
