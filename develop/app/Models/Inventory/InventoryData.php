<?php

/**
 * 在庫モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Inventory;

use App\Consts\DB\Inventory\InventoryDataConst;
use App\Enums\OrderStatus;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterUser;
use App\Models\Master\MasterWarehouse;
use App\Models\Receive\OrdersReceived;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 在庫データモデル
 */
class InventoryData extends Model
{
    protected $table = 'inventory_datas';

    /**
     * factory
     */
    use HasFactory;

    /**
     * 在庫データオブザーバ使用
     */
    use InventoryDataObservable;

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
     * 検索条件
     */
    protected array $condition = [
        'id' => null,
        'inout_date' => null,
        'from_warehouse_id' => null,
        'to_warehouse_id' => null,
    ];

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'orders_received_number',
        'orders_received_details_sort',
        'inout_date',
        'inout_status',
        'from_warehouse_id',
        'to_warehouse_id',
        'employee_id',
        'note',
        'updated_id',
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
        // 検索条件を$target_* 形式の可変変数セット 検索条件が増える際は$conditionへ項目追加
        foreach ($this->condition as $key => $value) {
            ${'target_' . $key} = $search_condition_input_data[$key] ?? $value;
        }

        return $query
            // IDで絞り込み
            ->when($target_id !== null, function ($query) use ($target_id) {
                return $query->inventoryDataId($target_id);
            })
            // 入出庫日で絞り込み
            ->searchInoutDate($target_inout_date)
            // from_warehouse_idで絞り込み
            ->warehouseId($target_from_warehouse_id, 'from')
            // to_warehouse_idで絞り込み
            ->warehouseId($target_to_warehouse_id, 'to');
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
     * 指定した日付のレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param string|null $target_inout_date_start
     * @param string|null $target_inout_date_end
     * @return Builder
     */
    public function scopeInoutDate(Builder $query,
        ?string $target_inout_date_start,
        ?string $target_inout_date_end
    ): Builder {
        if ($target_inout_date_start === null && $target_inout_date_end === null) {
            return $query;
        }

        if ($target_inout_date_start !== null && $target_inout_date_end === null) {
            return $query->where('inout_date', '>=', $target_inout_date_start);
        }

        if ($target_inout_date_start === null && $target_inout_date_end !== null) {
            return $query->where('inout_date', '<=', $target_inout_date_end);
        }

        return $query->whereBetween('inout_date', [$target_inout_date_start, $target_inout_date_end]);
    }

    /**
     * 指定した日付のレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param string|null $target_inout_date_start
     * @param string|null $target_inout_date_end
     * @return Builder
     */
    public function scopeOrderDate(Builder $query,
        ?string $target_inout_date_start, ?string $target_inout_date_end): Builder
    {
        if ($target_inout_date_start === null && $target_inout_date_end === null) {
            return $query;
        }

        if ($target_inout_date_start !== null && $target_inout_date_end === null) {
            return $query->where('inout_date', '>=', $target_inout_date_start);
        }

        if ($target_inout_date_start === null && $target_inout_date_end !== null) {
            return $query->where('inout_date', '<=', $target_inout_date_end);
        }

        return $query->whereBetween('inout_date', [$target_inout_date_start, $target_inout_date_end]);
    }

    /**
     * 指定した状態のレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $target_inout_status
     * @return Builder
     */
    public function scopeInoutStatus(Builder $query, array $target_inout_status): Builder
    {
        if ($target_inout_status === null) {
            return $query;
        }

        return $query->whereIn('inout_status', $target_inout_status);
    }

    /**
     * 指定した倉庫IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int|null $warehouse_id
     * @param string $from_or_to
     * @return Builder
     */
    public function scopeWarehouseId(Builder $query, ?int $warehouse_id, string $from_or_to): Builder
    {
        return $query
            ->when($warehouse_id !== null, function ($query) use ($warehouse_id, $from_or_to) {
                return $query->where($from_or_to . '_warehouse_id', $warehouse_id);
            });
    }

    /**
     * 指定した倉庫ID(FromかTo)のレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int|null $target_warehouse_id
     * @return Builder
     */
    public function scopeFromOrToWarehouseId(Builder $query, ?int $target_warehouse_id): Builder
    {
        return $query
            ->when(!empty($target_warehouse_id), function ($query) use ($target_warehouse_id) {
                return $query->where('from_warehouse_id', $target_warehouse_id)
                    ->orWhere('to_warehouse_id', $target_warehouse_id);
            });
    }

    /**
     * 指定した入出庫日に絞り込むクエリースコープ
     *
     * @param Builder $query
     * @param array|null $date
     * @return Builder
     */
    public function scopeSearchInoutDate(Builder $query, ?array $date): Builder
    {
        return $query
            ->when($date !== null, function ($query) use ($date) {
                return $query->inoutDate($date['start'] ?? null, $date['end'] ?? null);
            });
    }

    // endregion eloquent-scope

    // region eloquent-relationships

    /**
     * 担当者マスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mEmployee(): BelongsTo
    {
        return $this->belongsTo(MasterEmployee::class, 'employee_id');
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
     * ユーザーマスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mUpdated(): BelongsTo
    {
        return $this->belongsTo(MasterUser::class, 'updated_id');
    }

    /**
     * 在庫データ詳細 テーブルとのリレーション
     *
     * @return HasMany
     */
    public function inventoryDataDetail(): HasMany
    {
        return $this->hasMany(InventoryDataDetail::class, 'inventory_data_id');
    }

    /**
     * m_warehouse テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mWarehouseFrom(): BelongsTo
    {
        return $this->belongsTo(MasterWarehouse::class, 'from_warehouse_id');
    }

    /**
     * m_warehouse テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mWarehouseTo(): BelongsTo
    {
        return $this->belongsTo(MasterWarehouse::class, 'to_warehouse_id');
    }

    /**
     * 状態履歴 テーブルとのリレーション
     *
     * @return HasMany
     */
    public function inventoryDatStatusHistory(): HasMany
    {
        return $this->hasMany(InventoryDataStatusHistory::class, 'inventory_data_id')
            ->orderByDesc('created_at');
    }

    /**
     * 受注伝票 テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function ordersReceived(): BelongsTo
    {
        return $this->belongsTo(OrdersReceived::class, 'orders_received_number', 'id')
            ->with(['mCustomer', 'mBranch', 'mRecipient']);
    }

    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 発注番号（ゼロ埋め）を取得
     *
     * @return string
     */
    public function getOrderNumberZerofillAttribute(): string
    {
        $length = InventoryDataConst::ORDER_NUMBER_MAX_LENGTH;

        return sprintf("%0{$length}d", $this->order_number);
    }

    /**
     * 見積日付（「YYYY/MM/DD]」形式）を取得
     *
     * @return string|null
     */
    public function getEstimateDateSlashAttribute(): ?string
    {
        if (is_null($this->inout_date)) {
            return null;
        }

        return Carbon::parse($this->inout_date)->format('Y/m/d');
    }

    /**
     * 担当者名を取得
     *
     * @return string
     */
    public function getEmployeeNameAttribute(): string
    {
        return $this->mEmployee->name ?? '';
    }

    /**
     * 状態を取得
     *
     * @return string
     */
    public function getOrderStatusNameAttribute(): ?string
    {
        if (is_null($this->inout_status)) {
            return null;
        }

        return OrderStatus::getDescription($this->inout_status);
    }

    /**
     * ユーザー名を取得
     *
     * @return string
     */
    public function getUpdatedNameAttribute(): string
    {
        return $this->mUpdated->name ?? '';
    }

    /**
     * 更新日（「YYYY/MM/DD]」形式）を取得
     *
     * @return string|null
     */
    public function getUpdatedAtSlashAttribute(): ?string
    {
        if (is_null($this->updated_at)) {
            return null;
        }

        return Carbon::parse($this->updated_at)->format('Y/m/d H:i:s');
    }

    /**
     * From倉庫のコードを取得
     *
     * @return string
     */
    public function getFromWarehouseCodeAttribute(): string
    {
        return $this->mWarehouseFrom->code ?? '';
    }

    /**
     * To倉庫のコードを取得
     *
     * @return string
     */
    public function getToWarehouseCodeAttribute(): string
    {
        return $this->mWarehouseTo->code ?? '';
    }

    /**
     * From倉庫名を取得
     *
     * @return string
     */
    public function getFromWarehouseNameAttribute(): string
    {
        return $this->mWarehouseFrom->name ?? '';
    }

    /**
     * To倉庫名を取得
     *
     * @return string
     */
    public function getToWarehouseNameAttribute(): string
    {
        return $this->mWarehouseTo->name ?? '';
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
            ->with(['mProduct', 'mWarehouseFrom', 'mWarehouseTo', 'mUpdated'])
            ->orderByDesc('inout_date')   // 入出庫日の降順
            ->orderByDesc('id')           // IDの降順
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.sales_order.page_count'));
    }

    /**
     * 検索結果を取得(締在庫)
     *
     * @param array $search_condition_input_data
     * @return Collection
     */
    public static function getSearchResultClosingStocks(array $search_condition_input_data): Collection
    {
        return self::query()
            ->leftjoin('inventory_data_details', function ($join) {
                $join->on('inventory_datas.id', '=', 'inventory_data_details.inventory_data_id');
            })
            ->where('product_id', $search_condition_input_data['product_id'])
            ->whereNull('inventory_data_details.deleted_at')
            ->with(['mWarehouseFrom', 'mWarehouseTo', 'mUpdated'])
            ->orderByDesc('inout_date')   // 入出庫日の降順
            ->orderByDesc('id')           // IDの降順
            ->searchCondition($search_condition_input_data)
            ->get();
    }

    /**
     * 仕入と納品の合計を取得
     *
     * @param array $search_condition_input_data
     * @return int
     */
    public static function getStockTotal(array $search_condition_input_data): int
    {
        $target_inout_date = $search_condition_input_data['inout_date'] ?? null; // 入出庫日
        $target_product_id = $search_condition_input_data['product_id'] ?? null; // 商品ID
        $target_warehouse_ids = MasterWarehouse::getDoNotControlInventoryList(); // 在庫管理しない倉庫ID

        return self::getFromQuantityTotal($target_inout_date, $target_warehouse_ids, $target_product_id)
            - self::getToQuantityTotal($target_inout_date, $target_warehouse_ids, $target_product_id);
    }

    /**
     * 倉庫の入出庫合計を取得
     *
     * @param array $search_condition_input_data
     * @return int
     */
    public static function getStockTotalByWarehouse(array $search_condition_input_data): int
    {
        $target_inout_date = $search_condition_input_data['inout_date'] ?? null; // 入出庫日
        $target_product_id = $search_condition_input_data['product_id'] ?? null; // 商品ID
        $target_warehouse_ids[] = $search_condition_input_data['warehouse_id'] ?? 0; // 倉庫ID

        return self::getToQuantityTotal($target_inout_date, $target_warehouse_ids, $target_product_id)
            - self::getFromQuantityTotal($target_inout_date, $target_warehouse_ids, $target_product_id);
    }

    /**
     * 仕入れの在庫数を取得
     *
     * @param array $target_inout_date
     * @param array $warehouse_ids
     * @param string $target_product_id
     * @return Collection
     */
    public static function getFromQuantityTotal(array $target_inout_date, array $warehouse_ids, ?string $target_product_id): string
    {
        $inventory_id_list = self::getInventoryIdList($target_inout_date, $warehouse_ids, $target_product_id, 'from');

        // inventory_idが取れなかったら'0'を返す
        return $inventory_id_list ? InventoryDataDetail::getQuantityTotal($target_product_id, $inventory_id_list) : '0';
    }

    /**
     * 納品の在庫数を取得
     *
     * @param array|null $target_inout_date
     * @param array $warehouse_ids
     * @param string $target_product_id
     * @return Collection
     */
    public static function getToQuantityTotal(?array $target_inout_date, array $warehouse_ids, ?string $target_product_id): string
    {
        $inventory_id_list = self::getInventoryIdList($target_inout_date, $warehouse_ids, $target_product_id, 'to');

        // inventory_idが取れなかったら'0'を返す
        return $inventory_id_list ? InventoryDataDetail::getQuantityTotal($target_product_id, $inventory_id_list) : '0';
    }

    /**
     * 倉庫IDのリストを取得
     *
     * @param array|null $target_inout_date
     * @param array $warehouse_ids
     * @param string $target_product_id
     * @param string $from_or_to
     * @return array
     */
    public static function getInventoryIdList(?array $target_inout_date, array $warehouse_ids, ?string $target_product_id, string $from_or_to = 'all'): array
    {
        return self::query()
            ->with(['inventoryDataDetail'])
            ->when(!empty($target_product_id), function ($query) use ($target_product_id) {
                $query->whereHas('inventoryDataDetail', function ($query) use ($target_product_id) {
                    return $query->where('product_id', $target_product_id);
                });
            })
            // 入出庫日で絞り込み
            ->searchInoutDate($target_inout_date)
            ->when($from_or_to === 'from', function ($query) use ($warehouse_ids) {
                return $query->whereIn('from_warehouse_id', $warehouse_ids);
            })
            ->when($from_or_to === 'to', function ($query) use ($warehouse_ids) {
                return $query->whereIn('to_warehouse_id', $warehouse_ids);
            })
            ->pluck('id')
            ->toArray();
    }

    /**
     * 検索結果を取得(詳細)
     *
     * @param array $search_condition_input_data
     * @return Collection
     */
    public static function getSearchResultDetail(array $search_condition_input_data): Collection
    {
        return self::query()
            ->leftJoin('inventory_data_details', 'inventory_datas.id', '=', 'inventory_data_id')
            ->oldest('inout_date')   // 入出庫日の昇順
            ->oldest('id')           // IDの昇順
            ->searchCondition($search_condition_input_data)
            ->get();
    }

    // endregion static method
}
