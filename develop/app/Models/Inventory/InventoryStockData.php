<?php

/**
 * 現在庫データモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Inventory;

use App\Enums\InventoryType;
use App\Enums\IsControlInventory;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterUser;
use App\Models\Master\MasterWarehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 現在庫データモデル
 */
class InventoryStockData extends Model
{
    protected $table = 'inventory_stock_datas';

    /**
     * factory
     */
    use HasFactory;

    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    /**
     * 日付を変形する属性
     *
     * @var array
     */
    protected $dates = [
        /** 日付 カラム */
        'inout_date',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'inventory_stocks',
    ];

    /**
     * キャスト
     */
    protected $casts = [
        'inventory_stocks' => 'integer',
    ];

    // region eloquent-scope

    /**
     * 指定した検索条件だけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $search_condition_input_data
     * @return Builder
     */
    public function scopeSearchCondition(Builder $query, array $search_condition_input_data): Builder
    {
        $target_id = $search_condition_input_data['id'] ?? null; // ID
        $target_product_id = $search_condition_input_data['product_code'] ?? null; // 商品

        return $query
            // IDで絞り込み
            ->when(isset($target_id), function ($query) use ($target_id) {
                return $query->inventoryStockDataId($target_id);
            })
            // 商品で絞り込み
            ->when(isset($target_product_id), function ($query) use ($target_product_id) {
                return $query->inventoryStockData($target_product_id);
            });
    }

    /**
     * 指定した検索条件(在庫管理する倉庫)だけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $search_condition_input_data
     * @return Builder
     */
    public function scopeSearchDoControlWarehouse(Builder $query, array $search_condition_input_data): Builder
    {
        $target_product_id = $search_condition_input_data['product_id'] ?? null; // 商品
        $target_warehouse_id = $search_condition_input_data['warehouse_id'] ?? null; // 倉庫

        return $query
            ->with(['mProduct', 'mWarehouse', 'mUser'])
            ->whereHas('mWarehouse', function ($query) {
                return $query->where('is_control_inventory', IsControlInventory::DO_CONTROL);
            })
            ->when(isset($target_product_id), function ($query) use ($target_product_id) {
                $query->where('product_id', $target_product_id);
            })
            ->when(isset($target_warehouse_id), function ($query) use ($target_warehouse_id) {
                $query->where('warehouse_id', $target_warehouse_id);
            });
    }

    /**
     * 指定したIDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int $target_id
     * @return Builder
     */
    public function scopeInventoryDataId(Builder $query, int $target_id): Builder
    {
        return $query->where('id', $target_id);
    }

    /**
     * 指定した状態のレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $target_product_id
     * @return Builder
     */
    public function scopeProducts(Builder $query, array $target_product_id): Builder
    {
        return $query->whereIn('product_id', $target_product_id);
    }

    // endregion eloquent-scope

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
     * m_warehouse テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mWarehouse(): BelongsTo
    {
        return $this->belongsTo(MasterWarehouse::class, 'warehouse_id');
    }

    /**
     * ユーザーマスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mUser(): BelongsTo
    {
        return $this->belongsTo(MasterUser::class, 'updated_id');
    }

    /**
     * m_warehouse テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mWarehouses(): BelongsTo
    {
        return $this->belongsTo(MasterWarehouse::class, 'warehouse_id');
    }

    /**
     * 状態履歴 テーブルとのリレーション
     *
     * @return HasMany
     */
    public function inventoryStocksDatStatusHistory(): HasMany
    {
        return $this->hasMany(InventoryDataStatusHistory::class, 'inventory_stocks_data_id')
            ->latest();
    }

    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * ユーザー名を取得
     *
     * @return string
     */
    public function getUserNameAttribute(): string
    {
        return $this->mUser->name ?? '';
    }

    /**
     * 倉庫名を取得
     *
     * @return string
     */
    public function getWarehousesNameAttribute(): string
    {
        return $this->mWarehouse->name ?? '';
    }

    /**
     * 商品名を取得
     *
     * @return string
     */
    public function getProductNameAttribute(): string
    {
        return $this->mProduct->name ?? '';
    }
    // endregion eloquent-accessors

    // region static method

    /**
     * 検索結果を取得
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public static function getSearchResult(array $search_condition_input_data): LengthAwarePaginator
    {
        return self::query()
            ->with(['mProduct', 'mWarehouse', 'mUser'])
            ->where($search_condition_input_data)
            ->selectRaw('warehouse_id, round(sum(inventory_stocks)) as sum_inventory_stocks')
            ->groupBy('warehouse_id')
            ->oldest('warehouse_id')
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * 検索結果を取得(在庫管理する倉庫のみ)
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public static function getSearchResultWhereNotIn(array $search_condition_input_data): LengthAwarePaginator
    {
        return self::query()
            ->searchDoControlWarehouse($search_condition_input_data)
            ->selectRaw('warehouse_id, round(sum(inventory_stocks)) as sum_inventory_stocks')
            ->groupBy('warehouse_id')
            ->oldest('warehouse_id')
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * 在庫数の合計を取得(在庫管理する倉庫のみ)
     *
     * @param array $search_condition_input_data
     * @return string
     */
    public static function getInventoryStocksTotal(array $search_condition_input_data): string
    {
        return self::query()
            ->searchDoControlWarehouse($search_condition_input_data)
            ->sum('inventory_stocks');
    }

    /**
     * 検索結果を取得(在庫調整)
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public static function getSearchResultAdjustStock(array $search_condition_input_data): LengthAwarePaginator
    {
        $target_id = $search_condition_input_data['code'] ?? null;
        $target_product_id = $search_condition_input_data['product_id'] ?? null;
        $target_warehouse_id = $search_condition_input_data['warehouse_id'] ?? null;

        return self::query()
            ->with(['mProduct', 'mWarehouse', 'mUser'])
            ->whereNotIn('warehouse_id', [InventoryType::INVENTORY_IN, InventoryType::INVENTORY_OUT])
            ->when(isset($target_id), function ($query) use ($target_id) {
                return $query->id($target_id['start'] ?? null, $target_id['end'] ?? null);
            })
            ->when(isset($target_product_id), function ($query) use ($target_product_id) {
                return $query->where('product_id', $target_product_id);
            })
            ->when(isset($target_warehouse_id), function ($query) use ($target_warehouse_id) {
                return $query->where('warehouse_id', $target_warehouse_id);
            })
            ->oldest('warehouse_id')
            ->oldest('warehouse_id')
            ->oldest('product_id')
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * @param $productId
     * @param $warehouseId
     * @return InventoryStockData|null
     */
    public static function getDataByProductWareHouse($productId, $warehouseId): ?self
    {
        return self::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
    }

    /**
     * 指定したIDのレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param int|null $target_id_start
     * @param int|null $target_id_end
     * @return Builder
     */
    public function scopeId(Builder $query, ?int $target_id_start, ?int $target_id_end): Builder
    {
        if (is_null($target_id_start) && is_null($target_id_end)) {
            return $query;
        }
        if (isset($target_id_start) && is_null($target_id_end)) {
            return $query->where('warehouse_id', '>=', $target_id_start);
        }
        if (is_null($target_id_start) && isset($target_id_end)) {
            return $query->where('warehouse_id', '<=', $target_id_end);
        }

        return $query->whereBetween('warehouse_id', [$target_id_start, $target_id_end]);
    }

    /**
     * リレーション情報を取得
     *
     * @return HasMany
     */
    public function productDetails(): HasMany
    {
        return $this->hasMany(InventoryStockData::class, 'product_id', 'id');
    }

    // endregion static method
}
