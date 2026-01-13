<?php

/**
 * 受注伝票モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Receive;

use App\Consts\DB\Receive\OrdersReceivedConst;
use App\EloquentBuilders\Receive\OrdersReceivedBuilder;
use App\Enums\OrderStatus;
use App\Enums\SalesConfirm;
use App\Models\Inventory\InventoryData;
use App\Models\Master\MasterBranch;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterHonorificTitle;
use App\Models\Master\MasterRecipient;
use App\Models\Master\MasterUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 受注伝票モデル
 */
class OrdersReceived extends Model
{
    /**
     * 受注伝票オブザーバ使用
     */
    use OrdersReceivedObservable;

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
        /** 受注日付 カラム */
        'order_date',
    ];

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'order_number',
        'estimate_date',
        'order_date',
        'delivery_date',
        'order_status',
        'customer_id',
        'customer_delivery_id',
        'branch_id',
        'recipient_id',
        'employee_id',
        'sales_total',
        'discount',
        'memo',
        'updated_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // region eloquent-scope

    /**
     * newEloquentBuilderをオーバーライド
     *
     * @param $query
     * @return OrdersReceivedBuilder
     */
    public function newEloquentBuilder($query): OrdersReceivedBuilder
    {
        return new OrdersReceivedBuilder($query);
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
     * 得意先マスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mCustomer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id')
            ->with('mBillingCustomer');
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
     * 発注伝票詳細 テーブルとのリレーション
     *
     * @return HasMany
     */
    public function ordersReceivedDetail(): HasMany
    {
        return $this->hasMany(OrdersReceivedDetail::class, 'orders_received_id');
    }

    /**
     * 支所マスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mBranch(): BelongsTo
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }

    /**
     * 納品先マスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mRecipient(): BelongsTo
    {
        return $this->belongsTo(MasterRecipient::class, 'recipient_id');
    }

    /**
     * 受注伝票 テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function inventoryData(): BelongsTo
    {
        return $this->belongsTo(InventoryData::class, 'id', 'orders_received_number');
    }

    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 受注番号（ゼロ埋め）を取得
     *
     * @return string
     */
    public function getOrderNumberZerofillAttribute(): string
    {
        $length = OrdersReceivedConst::ORDER_NUMBER_MAX_LENGTH;

        return sprintf("%0{$length}d", $this->order_number);
    }

    /**
     * 伝票日付（「YYYY/MM/DD]」形式）を取得
     *
     * @return string
     */
    public function getOrderDateSlashAttribute(): string
    {
        if (is_null($this->order_date)) {
            return '';
        }

        return Carbon::parse($this->order_date)->format('Y/m/d');
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
     * 得意先名を取得
     *
     * @return string
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->mCustomer->name ?? '';
    }

    /**
     * 状態を取得
     *
     * @return string
     */
    public function getOrderStatusNameAttribute(): string
    {
        if (is_null($this->order_status)) {
            return '';
        }

        return OrderStatus::getDescription($this->order_status);
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
     * 支所名を取得
     *
     * @return string
     */
    public function getBranchNameAttribute(): string
    {
        return $this->mBranch->name ?? '';
    }

    /**
     * 納品先名を取得
     *
     * @return string
     */
    public function getRecipientNameAttribute(): string
    {
        return $this->mRecipient->name ?? '';
    }

    /**
     * 納品先名を取得
     *
     * @return string
     */
    public function getRecipientNameKanaAttribute(): string
    {
        return $this->mRecipient->name_kana ?? '';
    }

    /**
     * 得意先名+支所名+敬称を取得
     *
     * @return string
     */
    public function getCnameBnameHtitleAttribute(): string
    {
        $honorific_title = new MasterHonorificTitle();

        return $this->customer_name . '　' . $this->branch_name . '　' . $honorific_title->name_fixed; // 敬称"様"固定
    }

    /**
     * 請求先IDを取得
     *
     * @return int
     */
    public function getCustomerIdByBillingCustomerAttribute(): int
    {
        return $this->getBillingCustomer()->id;
    }

    /**
     * 税計算区分を取得
     *
     * @return int
     */
    public function getTaxCalcTypeIdByBillingCustomerAttribute(): int
    {
        return $this->getBillingCustomer()->tax_calc_type_id;
    }

    /**
     * 請求先IDを取得
     *
     * @return int
     */
    public function getTaxRoundingMethodIdByBillingCustomerAttribute(): int
    {
        return $this->getBillingCustomer()->tax_rounding_method_id;
    }

    // endregion eloquent-accessors

    /**
     * 売上確定のフラグが立っていない明細があるかの判定
     *
     * @return bool
     */
    public function getSalesConfirmFlg(): bool
    {
        return $this
            ->ordersReceivedDetail
            ->where('sales_confirm', '<>', SalesConfirm::CONFIRM)
            ->isEmpty();
    }

    /**
     * 請求先データを取得
     *
     * @return Model
     */
    public function getBillingCustomer(): Model
    {
        return $this->mCustomer->mBillingCustomer;
    }
}
