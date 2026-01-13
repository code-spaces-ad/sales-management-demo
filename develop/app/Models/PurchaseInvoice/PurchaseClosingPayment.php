<?php

/**
 * 仕入締データ_支払伝票リレーションモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\PurchaseInvoice;

use App\Models\Trading\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 仕入締データ_支払伝票リレーションモデル
 */
class PurchaseClosingPayment extends Model
{
    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    protected $fillable = [
        'purchase_closing_id',
        'payment_id',
    ];

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'purchase_closing_payment';

    /**
     * @var string 主キーカラム
     */
    protected $primaryKey = 'purchase_closing_id';

    // region eloquent-relationships

    /**
     * 仕入締データ リレーション情報を取得
     *
     * @return HasOne
     */
    public function purchaseData(): HasOne
    {
        return $this->hasOne(PurchaseClosing::class, 'purchase_closing_id');
    }

    /**
     * 支払伝票 リレーション情報を取得
     *
     * @return HasOne
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'payment_id');
    }

    // endregion eloquent-relationships

    // region static method

    /**
     * 支払伝票 リレーション情報登録
     *
     * @param array $order_ids
     * @param int $purchase_closing_id
     */
    public static function createPaymentRelation(array $order_ids, int $purchase_closing_id): void
    {
        foreach ($order_ids as $order_id) {
            self::create(
                [
                    'purchase_closing_id' => $purchase_closing_id,
                    'payment_id' => $order_id,
                ]
            )->save();
        }
    }

    /**
     * 支払伝票 リレーション情報削除
     *
     * @param int $purchase_closing_id
     */
    public static function deletePaymentRelation(int $purchase_closing_id): void
    {
        self::where('purchase_closing_id', $purchase_closing_id)->delete();
    }
    // endregion static method
}
