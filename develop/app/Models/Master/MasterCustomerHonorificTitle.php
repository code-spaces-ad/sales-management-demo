<?php

/**
 * 商品_単位リレーションモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 得意先_敬称リレーションモデル
 */
class MasterCustomerHonorificTitle extends Model
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
    protected $table = 'm_customers_honorific_titles';

    /**
     * @var string 主キーカラム
     */
    protected $primaryKey = 'customer_id';

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'honorific_title_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    // region eloquent-relationships

    /**
     * 得意先テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mCustomer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    /**
     * 敬称テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mHonorificTitle(): BelongsTo
    {
        return $this->belongsTo(MasterHonorificTitle::class, 'honorific_title_id');
    }

    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 敬称を取得
     *
     * @return string
     */
    public function getHonorificTitleNameAttribute(): ?string
    {
        return $this->mHonorificTitle->name ?? null;
    }

    // endregion eloquent-accessors
}
