<?php

/**
 * 入金伝票_手形リレーションモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Sale;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 入金伝票_手形リレーションモデル
 */
class DepositOrderBill extends Model
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
    protected $table = 'deposit_order_bill';

    /**
     * @var string 主キーカラム
     */
    protected $primaryKey = 'deposit_order_id';

    /**
     * 日付を変形する属性
     *
     * @var array
     */
    protected $dates = [
        /** 手形期日 カラム */
        'bill_date',
    ];

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'deposit_order_id',
        'bill_date',
        'bill_number',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // region eloquent-relationships

    /**
     * 入金伝票 テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function depositOrder(): BelongsTo
    {
        return $this->belongsTo(DepositOrder::class, 'deposit_order_id');
    }

    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 手形期日（「YYYY/MM/DD]」形式）を取得
     *
     * @return string|null
     */
    public function getBillDateSlashAttribute(): ?string
    {
        if (is_null($this->bill_date)) {
            return null;
        }

        return Carbon::parse($this->bill_date)->format('Y/m/d');
    }

    // endregion eloquent-accessors
}
