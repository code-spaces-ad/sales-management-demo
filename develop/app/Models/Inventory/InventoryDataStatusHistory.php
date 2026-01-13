<?php

/**
 * 発注伝票状態履歴モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Inventory;

use App\Enums\OrderStatus;
use App\Models\Master\MasterUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 発注伝票状態履歴モデル
 */
class InventoryDataStatusHistory extends Model
{
    /**
     * factory
     */
    use HasFactory;

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    public const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'inventory_data_status_history';

    // region eloquent-relationships

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inventory_data_id',
        'inout_status',
        'updated_id',
    ];

    /**
     * 発注伝票 テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function inventorydata(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'inventory_data_id');
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

    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 状態名を取得
     *
     * @return string|null
     */
    public function getOrderStatusNameAttribute(): ?string
    {
        if (is_null($this->order_status)) {
            return null;
        }

        return OrderStatus::getDescription($this->order_status);
    }

    /**
     * 更新者名を取得
     *
     * @return string|null
     */
    public function getUpdatedUserNameAttribute(): ?string
    {
        if (is_null($this->mUser)) {
            return null;
        }

        return $this->mUser->name ?? '';
    }

    /**
     * 作成日時（「YYYY/MM/DD H:i:s」形式）を取得
     *
     * @return string|null
     */
    public function getCreatedAtSlashAttribute(): ?string
    {
        if (is_null($this->created_at)) {
            return null;
        }

        return Carbon::parse($this->created_at)->format('Y/m/d H:i:s');
    }

    // endregion eloquent-accessors
}
