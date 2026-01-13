<?php

/**
 * 売上伝票詳細モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Sale;

use App\Enums\TaxType;
use App\Helpers\TaxHelper;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterRoundingMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 売上伝票詳細モデル
 */
class SalesOrderDetail extends Model
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
    protected $primaryKey = 'sales_order_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sales_order_id',
        'product_id',
        'product_name',
        'unit_price_decimal_digit',
        'quantity_decimal_digit',
        'quantity_rounding_method_id',
        'amount_rounding_method_id',
        'quantity',
        'unit_name',
        'unit_price',
        'discount',
        'sub_total',
        'sub_total_tax',
        'tax_type_id',
        'purchase_unit_price',
        'consumption_tax_rate',
        'reduced_tax_flag',
        'rounding_method_id',
        'gross_profit',
        'note',
        'sort',
    ];

    // region eloquent-relationships

    /**
     * sales_orders テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
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

    /**
     * m_rounding_methods テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mRoundingMethod(): BelongsTo
    {
        return $this->belongsTo(MasterRoundingMethod::class, 'rounding_method_id');
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
    public function getQuantityDigitCutAttribute(): string
    {
        $quantity_decimal_digit = $this->mProduct->quantity_decimal_digit ?? 0;
        $digit_cut = -5;
        if ($quantity_decimal_digit > 0) {
            $digit_cut = (4 - $quantity_decimal_digit) * -1;
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
        $digit_cut = -5;
        if ($unit_price_decimal_digit > 0) {
            $digit_cut = (4 - $unit_price_decimal_digit) * -1;
        }

        return substr($this->unit_price, 0, $digit_cut);
    }

    /**
     * 明細の消費税額
     *
     * @return float
     */
    public function getTaxAttribute(): float
    {
        $tax = 0;
        // 非課税
        if ($this->consumption_tax_rate === 0) {
            return 0;
        }
        // 外税
        if ($this->tax_type_id === TaxType::OUT_TAX) {
            return TaxHelper::getTax($this->unit_price, $this->consumption_tax_rate, $this->rounding_method_id);
        }
        // 内税
        if ($this->tax_type_id === TaxType::IN_TAX) {
            // 内税計算はいったん切捨て端数処理
            return TaxHelper::getInTax($this->unit_price, $this->consumption_tax_rate, $this->rounding_method_id);
        }

        return $tax;
    }
    // endregion eloquent-accessors
}
