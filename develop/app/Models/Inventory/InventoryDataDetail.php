<?php

/**
 * 在庫詳細モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Inventory;

use App\Enums\IsControlInventory;
use App\Models\Master\MasterRoundingMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 在庫詳細モデル
 */
class InventoryDataDetail extends Model
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
    protected $primaryKey = 'inventory_data_id';

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'inventory_data_id',
        'product_id',
        'product_name',
        'quantity',
        'note',
        'sort',
    ];

    // region eloquent-relationships

    /**
     * 在庫 テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function InventoryData(): BelongsTo
    {
        return $this->belongsTo(InventoryData::class, 'id');
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
     * 現在庫データ テーブルとのリレーション
     *
     * @return HasMany
     */
    public function inventoryStockData(): HasMany
    {
        return $this->hasMany(InventoryStockData::class, 'product_id', 'product_id')
            ->with(['mWarehouse']);
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
     * 数量の合計を取得
     *
     * @param string $target_product_id
     * @param array $inventory_data_list
     * @return string
     */
    public static function getQuantityTotal(string $target_product_id, array $inventory_data_list): string
    {
        return self::query()
            ->when(!empty($inventory_data_list), function ($query) use ($inventory_data_list) {
                return $query->whereIn('inventory_data_id', $inventory_data_list);
            })
            ->where('product_id', $target_product_id)
            ->sum('quantity');
    }

    /**
     * 現在個数の合計を取得(在庫管理する倉庫のみ)
     *
     * @return string
     */
    public function getInventoryStockDataAttribute(): string
    {
        return $this->inventoryStockData()
            ->whereHas('mWarehouse', function ($query) {
                return $query->where('is_control_inventory', IsControlInventory::DO_CONTROL);
            })
            ->sum('inventory_stocks');
    }

    // endregion eloquent-accessors
}
