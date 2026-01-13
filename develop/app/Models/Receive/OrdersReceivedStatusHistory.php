<?php

/**
 * 発注伝票状態履歴モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Receive;

use App\Enums\OrderStatus;
use App\Models\Master\MasterUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 発注伝票状態履歴モデル
 */
class OrdersReceivedStatusHistory extends Model
{
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
    protected $table = 'orders_received_status_history';

    // region eloquent-relationships

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'orders_received_id',
        'order_status',
        'updated_id',
    ];

    /**
     * 発注伝票 テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function ordersReceived()
    {
        return $this->belongsTo(OrdersReceived::class, 'orders_received_id');
    }

    /**
     * ユーザーマスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mUser()
    {
        return $this->belongsTo(MasterUser::class, 'updated_id');
    }

    // endregion eloquent-relationships

    // region eloquent-

    /**
     * 状態名を取得
     *
     * @return string
     */
    public function getOrderStatusNameAttribute()
    {
        if (is_null($this->order_status)) {
            return null;
        }

        return OrderStatus::getDescription($this->order_status);
    }

    // endregion eloquent-accessors
}
