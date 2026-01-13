<?php

/**
 * 仕入締データ_仕入伝票リレーションモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\PurchaseInvoice;

use App\Models\Trading\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 仕入締データ_仕入伝票リレーションモデル
 */
class PurchaseClosingPurchaseOrder extends Model
{
    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    protected $fillable = [
        'purchase_closing_id',
        'purchase_order_id',
    ];

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'purchase_closing_purchase_order';

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
    public function chargeData(): HasOne
    {
        return $this->hasOne(PurchaseClosing::class, 'purchase_closing_id', 'id');
    }

    /**
     * 仕入伝票 リレーション情報を取得
     *
     * @return HasOne
     */
    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    // endregion eloquent-relationships

    // region static method

    /**
     * 仕入伝票 リレーション情報登録
     *
     * @param array $order_ids
     * @param int $purchase_closing_id
     */
    public static function createPurchaseRelation(array $order_ids, int $purchase_closing_id): void
    {
        foreach ($order_ids as $order_id) {
            self::create(
                [
                    'purchase_closing_id' => $purchase_closing_id,
                    'purchase_order_id' => $order_id,
                ]
            )->save();
        }
    }

    /**
     * 仕入伝票 リレーション情報削除
     *
     * @param int $purchase_closing_id
     */
    public static function deletePurchaseRelation(int $purchase_closing_id): void
    {
        self::where('purchase_closing_id', $purchase_closing_id)->delete();
    }
    // endregion static method
}
