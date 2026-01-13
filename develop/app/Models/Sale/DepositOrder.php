<?php

/**
 * 入金伝票モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Sale;

use App\Consts\DB\Sale\DepositOrderConst;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 入金伝票モデル
 */
class DepositOrder extends Model
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
     * 検索条件
     */
    protected array $condition = [
        'order_number' => null,
        'order_date' => null,
        'customer_id' => null,
        'transaction_type' => null,
        'department_id' => null,
        'office_facility_id' => null,
    ];

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'order_number',
        'transaction_type_id',
        'order_date',
        'customer_id',
        'billing_customer_id',
        'department_id',
        'office_facilities_id',
        'deposit',
        'note',
        'closing_at',
        'memo',
        'creator_id',
        'updated_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // region eloquent-scope

    /**
     * 指定した検索条件だけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $search_condition_input_data
     * @return Builder
     */
    public function scopeSearchCondition(Builder $query, array $search_condition_input_data): Builder
    {
        // 検索条件を$target_* 形式の可変変数セット 検索条件が増える際は$conditionへ項目追加
        foreach ($this->condition as $key => $value) {
            ${'target_' . $key} = $search_condition_input_data[$key] ?? $value;
        }

        return $query
            // 伝票番号で絞り込み
            ->when($target_order_number !== null, function ($query) use ($target_order_number) {
                return $query->where('order_number', $target_order_number);
            })
            // 種別で絞り込み
            ->when($target_transaction_type !== null, function ($query) use ($target_transaction_type) {
                return $query->transactionType($target_transaction_type);
            })
            // 伝票日付で絞り込み
            ->when($target_order_date !== null, function ($query) use ($target_order_date) {
                return $query->orderDate(
                    $target_order_date['start'] ?? null,
                    $target_order_date['end'] ?? null
                );
            })
            // 得意先IDで絞り込み
            ->when($target_customer_id !== null, function ($query) use ($target_customer_id) {
                return $query->customerId($target_customer_id);
            })
            // 部門IDで絞り込み
            ->when($target_department_id !== null, function ($query) use ($target_department_id) {
                return $query->departmentId($target_department_id);
            })
            // 事業所IDで絞り込み
            ->when($target_office_facility_id !== null, function ($query) use ($target_office_facility_id) {
                return $query->officeFacilityId($target_office_facility_id);
            });
    }

    /**
     * 指定した得意先IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int $target_customer_id
     * @return Builder
     */
    public function scopeCustomerId(Builder $query, int $target_customer_id): Builder
    {
        return $query->where('customer_id', $target_customer_id);
    }

    /**
     * 指定した種別のレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $target_transaction_type
     * @return Builder
     */
    public function scopeTransactionType(Builder $query, array $target_transaction_type): Builder
    {
        return $query->whereIn('transaction_type_id', $target_transaction_type);
    }

    /**
     * 指定した部門IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int $target_department_id
     * @return Builder
     */
    public function scopeDepartmentId(Builder $query, int $target_department_id): Builder
    {
        return $query->where('department_id', $target_department_id);
    }

    /**
     * 指定した事業所IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int $target_office_facility_id
     * @return Builder
     */
    public function scopeOfficeFacilityId(Builder $query, int $target_office_facility_id): Builder
    {
        return $query->where('office_facilities_id', $target_office_facility_id);
    }

    /**
     * 指定した伝票日付のレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param string|null $target_order_date_start
     * @param string|null $target_order_date_end
     * @return Builder
     */
    public function scopeOrderDate(Builder $query, ?string $target_order_date_start, ?string $target_order_date_end): Builder
    {
        if ($target_order_date_start === null && $target_order_date_end === null) {
            return $query;
        }

        if ($target_order_date_start !== null && $target_order_date_end === null) {
            return $query->where('order_date', '>=', $target_order_date_start);
        }

        if ($target_order_date_start === null && $target_order_date_end !== null) {
            return $query->where('order_date', '<=', $target_order_date_end);
        }

        return $query->whereBetween('order_date', [$target_order_date_start, $target_order_date_end]);
    }

    // endregion eloquent-scope

    // region eloquent-relationships

    /**
     * 得意先マスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mCustomer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    /**
     * 部門マスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterDepartment::class, 'department_id');
    }

    /**
     * 事業所マスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mOfficeFacilities(): BelongsTo
    {
        return $this->belongsTo(MasterOfficeFacility::class, 'office_facilities_id');
    }

    /**
     * 入力伝票詳細リレーション情報を取得
     *
     * @return hasOne
     */
    public function depositOrderDetail(): hasOne
    {
        return $this->hasOne(DepositOrderDetail::class);
    }

    /**
     * 入金伝票_手形リレーション情報を取得
     *
     * @return hasOne
     */
    public function depositOrderBill(): hasOne
    {
        return $this->hasOne(DepositOrderBill::class);
    }

    /**
     * ユーザーマスター テーブルとのリレーション(登録者用)
     *
     * @return BelongsTo
     */
    public function mCreator(): BelongsTo
    {
        return $this->belongsTo(MasterUser::class, 'creator_id');
    }

    /**
     * ユーザーマスター テーブルとのリレーション(更新者用)
     *
     * @return BelongsTo
     */
    public function mUpdated(): BelongsTo
    {
        return $this->belongsTo(MasterUser::class, 'updated_id');
    }
    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 伝票番号（ゼロ埋め）を取得
     *
     * @return string
     */
    public function getOrderNumberZerofillAttribute(): string
    {
        $length = DepositOrderConst::ORDER_NUMBER_MAX_LENGTH;

        return sprintf("%0{$length}d", $this->order_number);
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
     * 回収金額（「,」区切り）を取得
     *
     * @return string|null
     */
    public function getDepositCommaAttribute(): ?string
    {
        if (is_null($this->deposit)) {
            return null;
        }

        return number_format($this->deposit);
    }

    /**
     * 登録者名を取得
     *
     * @return string
     */
    public function getCreatorNameAttribute(): string
    {
        return $this->mCreator->name ?? '';
    }

    /**
     * 更新者名を取得
     *
     * @return string
     */
    public function getUpdatedNameAttribute(): string
    {
        return $this->mUpdated->name ?? '';
    }

    /**
     * 伝票日付（「YYYY/MM/DD]」形式）を取得
     *
     * @return string|null
     */
    public function getOrderDateSlashAttribute(): ?string
    {
        if (is_null($this->order_date)) {
            return null;
        }

        return Carbon::parse($this->order_date)->format('Y/m/d');
    }

    /**
     * 登録日（「YYYY/MM/DD]」形式）を取得
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
    // endregion eloquent-accessors

    // region static method

    /**
     * 締対象となる得意先毎の入金伝票情報を取得
     *
     * @param array $deposit_order_ids
     * @param string $start_date
     * @param string $end_date
     * @return self
     */
    public static function getTargetClosingData(array $deposit_order_ids, string $start_date, string $end_date): self
    {
        return self::query()
            ->whereIn('deposit_orders.id', $deposit_order_ids)
            ->leftJoin('deposit_order_details AS detail', function ($join) {
                $join->on('deposit_orders.id', '=', 'detail.deposit_order_id');
                $join->whereNull('detail.deleted_at');
            })
            ->whereBetween('order_date', [$start_date, $end_date])
            ->whereNull('closing_at')
            ->get([
                // 入金用(現金/小切手/振込/手形/相殺)
                \DB::raw('SUM( detail.amount_cash + detail.amount_check + detail.amount_transfer + ' .
                    'detail.amount_bill + detail.amount_offset ) AS amount_deposit'),
                // 調整額(値引/手数料/その他)
                \DB::raw('SUM( amount_discount + amount_fee + amount_other) AS amount_offset'),
            ])
            ->first();
    }

    /**
     * 伝票締処理更新
     *
     * @param array $order_ids
     * @param Carbon $process_date
     */
    public static function updateClosingAt(array $order_ids, Carbon $process_date): void
    {
        self::query()
            ->whereIn('id', $order_ids)
            ->whereNull('closing_at')
            ->update([
                'closing_at' => $process_date,
            ]);
    }

    /**
     * 伝票締処理解除
     *
     * @param array $order_ids
     */
    public static function cancelClosingAt(array $order_ids): void
    {
        self::query()
            ->whereIn('id', $order_ids)
            ->update([
                'closing_at' => null,
            ]);
    }
    // endregion static method
}
