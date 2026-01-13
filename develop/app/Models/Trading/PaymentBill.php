<?php

/**
 * 支払伝票_手形リレーションモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Trading;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 支払伝票_手形リレーションモデル
 */
class PaymentBill extends Model
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
    protected $table = 'payment_bill';

    /**
     * @var string 主キーカラム
     */
    protected $primaryKey = 'payment_id';

    /**
     * 日付を変形する属性
     *
     * @var array
     */
    protected $dates = [
        /** 手形期日 カラム */
        'bill_date',
    ];

    // region eloquent-relationships

    /**
     * 支払伝票 テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function depositOrder(): BelongsTo
    {
        return $this->belongsTo(DepositOrder::class, 'payment_id');
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
