<?php

/**
 * 支払伝票詳細モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Trading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 支払伝票詳細モデル
 */
class PaymentDetail extends Model
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
     * テーブル名
     *
     * @var string
     */
    protected $table = 'payment_details';

    /**
     * @var string 主キーカラム
     */
    protected $primaryKey = 'payment_id';

    // region eloquent-relationships

    /**
     * payments テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 回収金額を取得
     * ※各金額の合計値を取得
     *
     * @return int
     */
    public function getTotalPaymentAttribute(): int
    {
        $amount_cols = [
            'amount_cash',
            'amount_check',
            'amount_transfer',
            'amount_bill',
            'amount_offset',
            'amount_discount',
            'amount_fee',
            'amount_other',
        ];

        $total_amount = 0;  // 合計値
        foreach ($amount_cols as $col) {
            $total_amount += $this->{$col} ?? 0;
        }

        return $total_amount;
    }

    /**
     * 金額_現金（「,」区切り）を取得
     *
     * @return string|null
     */
    public function getAmountCashCommaAttribute(): ?string
    {
        if (is_null($this->amount_cash)) {
            return null;
        }

        return number_format($this->amount_cash);
    }

    /**
     * 金額_小切手（「,」区切り）を取得
     *
     * @return string|null
     */
    public function getAmountCheckCommaAttribute(): ?string
    {
        if (is_null($this->amount_check)) {
            return null;
        }

        return number_format($this->amount_check);
    }

    /**
     * 金額_振込（「,」区切り）を取得
     *
     * @return string|null
     */
    public function getAmountTransferCommaAttribute(): ?string
    {
        if (is_null($this->amount_transfer)) {
            return null;
        }

        return number_format($this->amount_transfer);
    }

    /**
     * 金額_手形（「,」区切り）を取得
     *
     * @return string|null
     */
    public function getAmountBillCommaAttribute(): ?string
    {
        if (is_null($this->amount_bill)) {
            return null;
        }

        return number_format($this->amount_bill);
    }

    /**
     * 金額_相殺（「,」区切り）を取得
     *
     * @return string|null
     */
    public function getAmountOffsetCommaAttribute(): ?string
    {
        if (is_null($this->amount_offset)) {
            return null;
        }

        return number_format($this->amount_offset);
    }

    /**
     * 金額_値引（「,」区切り）を取得
     *
     * @return string|null
     */
    public function getAmountDiscountCommaAttribute(): ?string
    {
        if (is_null($this->amount_discount)) {
            return null;
        }

        return number_format($this->amount_discount);
    }

    /**
     * 金額_手数料（「,」区切り）を取得
     *
     * @return string|null
     */
    public function getAmountFeeCommaAttribute(): ?string
    {
        if (is_null($this->amount_fee)) {
            return null;
        }

        return number_format($this->amount_fee);
    }

    /**
     * 金額_その他（「,」区切り）を取得
     *
     * @return string|null
     */
    public function getAmountOtherCommaAttribute(): ?string
    {
        if (is_null($this->amount_other)) {
            return null;
        }

        return number_format($this->amount_other);
    }

    /**
     * 支払額を取得
     *
     * @return int
     */
    public function getPaymentAttribute(): int
    {
        $payment = $this->amount_cash ?? 0;
        $payment += $this->amount_check ?? 0;
        $payment += $this->amount_transfer ?? 0;
        $payment += $this->amount_bill ?? 0;
        $payment += $this->amount_offset ?? 0;

        return $payment;
    }

    /**
     * 調整額を取得
     *
     * @return int
     */
    public function getAdjustAttribute(): int
    {
        $adjust = $this->amount_discount ?? 0;
        $adjust += $this->amount_fee ?? 0;
        $adjust += $this->amount_other ?? 0;

        return $adjust;
    }

    /**
     * 支払額（「,」区切り）を取得
     *
     * @return string|null
     */
    public function getPaymentCommaAttribute(): ?string
    {
        if (is_null($this->payment)) {
            return null;
        }

        return number_format($this->payment);
    }

    /**
     * 調整額（「,」区切り）を取得
     *
     * @return string|null
     */
    public function getAdjustCommaAttribute(): ?string
    {
        if (is_null($this->adjust)) {
            return null;
        }

        return number_format($this->adjust);
    }

    // endregion eloquent-accessors
}
