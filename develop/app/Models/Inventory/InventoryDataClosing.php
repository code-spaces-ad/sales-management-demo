<?php

/**
 * 帳簿在庫数用モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Inventory;

use App\Enums\IsControlInventory;
use App\Models\Master\MasterWarehouse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryDataClosing extends Model
{
    protected $table = 'inventory_data_closing';

    /**
     * factory
     */
    use HasFactory;

    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'closing_ym',
        'closing_stocks',
    ];

    /**
     * m_warehouse テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mWarehouse(): BelongsTo
    {
        return $this->belongsTo(MasterWarehouse::class, 'warehouse_id');
    }

    /**
     * 指定した検索条件だけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $search_condition_input_data
     * @return Builder
     */
    public function scopeSearchCondition(Builder $query, array $search_condition_input_data): Builder
    {
        $target_warehouse_id = $search_condition_input_data['warehouse_id'] ?? null; // 倉庫ID
        $target_product_id = $search_condition_input_data['product_id'] ?? null; // 商品ID
        $target_closing_ym = $search_condition_input_data['closing_ym'] ?? null; // 締年月

        // closing_ym(YYYYmm形式)から先月を取得
        if (!is_null($target_closing_ym)) {
            $target_closing_ym = Carbon::parse($search_condition_input_data['closing_ym'] . '01')->format('Y-m-d');
            $target_closing_ym = Carbon::parse($target_closing_ym)->subMonthNoOverflow()->format('Ym');
        }

        return $query
            // 倉庫IDで絞り込み
            ->when($target_warehouse_id !== null, function ($query) use ($target_warehouse_id) {
                return $query->where('warehouse_id', $target_warehouse_id);
            })
            // 商品IDで絞り込み
            ->when($target_product_id !== null, function ($query) use ($target_product_id) {
                return $query->where('product_id', $target_product_id);
            })
            // 締年月で絞り込み
            ->when($target_closing_ym !== null, function ($query) use ($target_closing_ym) {
                return $query->where('closing_ym', $target_closing_ym);
            });
    }

    /**
     * 前月分の締在庫数の合計を取得(在庫管理する倉庫のみ)
     *
     * @param array $search_condition_input_data
     * @return string
     */
    public static function getClosingStocksTotalFromLastMonth(array $search_condition_input_data): string
    {
        return self::query()
            ->with(['mWarehouse'])
            ->whereHas('mWarehouse', function ($query) {
                return $query->where('is_control_inventory', IsControlInventory::DO_CONTROL);
            })
            ->searchCondition($search_condition_input_data)
            ->sum('closing_stocks');
    }
}
