<?php

/**
 * 請求データ_売上伝票リレーションモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Invoice;

use App\Models\Sale\SalesOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 請求データ_売上伝票リレーションモデル
 */
class ChargeDataSalesOrder extends Model
{
    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    protected $fillable = [
        'charge_data_id',
        'sales_order_id',
    ];

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'charge_data_sales_order';

    /**
     * @var string 主キーカラム
     */
    protected $primaryKey = 'charge_data_id';

    // region eloquent-relationships

    /**
     * 請求データ リレーション情報を取得
     *
     * @return HasOne
     */
    public function chargeData(): HasOne
    {
        return $this->hasOne(ChargeData::class, 'charge_data_id', 'id');
    }

    /**
     * 売上伝票 リレーション情報を取得
     *
     * @return HasOne
     */
    public function salesOrder(): HasOne
    {
        return $this->hasOne(SalesOrder::class, 'sales_order_id', 'id');
    }

    // endregion eloquent-relationships

    // region static method

    /**
     * 売上伝票 リレーション情報削除
     *
     * @param int $charge_data_id
     */
    public static function deleteOrderRelation(int $charge_data_id): void
    {
        self::where('charge_data_id', $charge_data_id)->delete();
    }
    // endregion static method
}
