<?php

/**
 * 受注伝票詳細モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Receive;

use App\Models\Master\MasterProduct;
use App\Models\Master\MasterRoundingMethod;
use App\Models\Master\MasterWarehouse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 受注伝票詳細モデル
 */
class OrdersReceivedDetail extends Model
{
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
    protected $primaryKey = 'orders_received_id';

    /**
     * キャスト
     */
    protected $casts = [
        'sales_confirm' => 'int',
    ];

    /**
     * 日付を変形する属性
     *
     * @var array
     */
    protected $dates = [
        /** 納品日付 カラム */
        'delivery_date',
    ];

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'orders_received_id',
        'warehouse_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_name',
        'unit_price',
        'consumption_tax_rate',
        'reduced_tax_flag',
        'rounding_method_id',
        'delivery_date',
        'note',
        'sales_confirm',
        'printing_date',
        'sort',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // region eloquent-relationships

    /**
     * 受注伝票 テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function ordersreceived(): BelongsTo
    {
        return $this->belongsTo(OrdersReceived::class, 'orders_received_id');
    }

    /**
     * 倉庫マスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mWarehouse(): BelongsTo
    {
        return $this->belongsTo(MasterWarehouse::class, 'warehouse_id');
    }

    public function mProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class, 'product_id');
    }

    /**
     * 倉庫名を取得
     *
     * @return string
     */
    public function getWarehouseNameAttribute(): string
    {
        return $this->mWarehouse->name ?? '';
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

    /**
     * 納品日付（「YYYY/MM/DD]」形式）を取得
     *
     * @return string
     */
    public function getDeliveryDateSlashAttribute(): string
    {
        if (is_null($this->delivery_date)) {
            return '';
        }

        return Carbon::parse($this->delivery_date)->format('Y/m/d');
    }
    // endregion eloquent-accessors
}
