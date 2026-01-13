<?php

/**
 * 支払伝票モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Trading;

use App\Consts\DB\Sale\DepositOrderConst;
use App\Enums\TransactionType;
use App\Helpers\DateHelper;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterSupplier;
use App\Models\Master\MasterUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 支払伝票モデル
 */
class Payment extends Model
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
    protected $table = 'payments';

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
        $target_transaction_type = $search_condition_input_data['transaction_type'] ?? null; // 種別
        $target_order_number = $search_condition_input_data['order_number'] ?? null;       // 伝票番号
        $target_order_date = $search_condition_input_data['order_date'] ?? null;           // 伝票日付
        $target_supplier_id = $search_condition_input_data['supplier_id'] ?? null;           // 仕入先
        $target_department_id = $search_condition_input_data['department_id'] ?? null; // 部門ID
        $target_office_facility_id = $search_condition_input_data['office_facility_id'] ?? null; // 事業所ID

        return $query
            // 伝票番号で絞り込み
            ->when(isset($target_order_number), function ($query) use ($target_order_number) {
                return $query->where('order_number', $target_order_number);
            })
            // 種別で絞り込み
            ->when($target_transaction_type !== null, function ($query) use ($target_transaction_type) {
                return $query->transactionType($target_transaction_type);
            })
            // 伝票日付で絞り込み
            ->when(isset($target_order_date), function ($query) use ($target_order_date) {
                return $query->orderDate($target_order_date['start'] ?? null, $target_order_date['end'] ?? null);
            })
            // 仕入先IDで絞り込み
            ->when(isset($target_supplier_id), function ($query) use ($target_supplier_id) {
                return $query->supplierid($target_supplier_id);
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
     * 指定した仕入先IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int $target_supplier_id
     * @return Builder
     */
    public function scopeSupplierId(Builder $query, int $target_supplier_id): Builder
    {
        return $query->where('supplier_id', $target_supplier_id);
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
     * 指定した伝票日付のレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param string|null $target_order_date_start
     * @param string|null $target_order_date_end
     * @return Builder
     */
    public function scopeOrderDate(Builder $query, ?string $target_order_date_start, ?string $target_order_date_end): Builder
    {
        if (is_null($target_order_date_start) && is_null($target_order_date_end)) {
            return $query;
        }

        if (isset($target_order_date_start) && is_null($target_order_date_end)) {
            return $query->where('order_date', '>=', $target_order_date_start);
        }

        if (is_null($target_order_date_start) && isset($target_order_date_end)) {
            return $query->where('order_date', '<=', $target_order_date_end);
        }

        return $query->whereBetween('order_date', [$target_order_date_start, $target_order_date_end]);
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

    // endregion eloquent-scope

    // region eloquent-relationships

    /**
     * 仕入先マスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mSupplier(): BelongsTo
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
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
    public function paymentDetail(): hasOne
    {
        return $this->hasOne(PaymentDetail::class);
    }

    /**
     * 支払伝票_手形リレーション情報を取得
     *
     * @return hasOne
     */
    public function paymentBill(): hasOne
    {
        return $this->hasOne(PaymentBill::class);
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
     * 仕入先名を取得
     *
     * @return string
     */
    public function getSupplierNameAttribute(): string
    {
        return $this->mSupplier->name ?? '';
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
     * 検索結果を取得
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public static function getSearchResult(array $search_condition_input_data): LengthAwarePaginator
    {
        return self::query()
            ->latest('order_number')
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.payment.page_count'));
    }

    /**
     * 検索結果を取得
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public static function getSearchResultTotal(array $search_condition_input_data): Collection
    {
        return self::query()
            ->join('payment_details', 'payments.id', '=', 'payment_details.payment_id')
            ->searchCondition($search_condition_input_data)
            ->get([
                DB::raw('SUM(payment) AS payment_amount'),
                DB::raw('SUM(amount_cash + amount_check + amount_transfer + amount_bill + amount_offset) AS payment'),
                DB::raw('SUM(amount_discount + amount_fee + amount_other) AS adjust_amount_total'),
            ]);
    }

    /**
     * 締対象の仕入先を取得
     *
     * @param string|null $supplier_id
     * @param string $charge_date
     * @param string $closing_date
     * @param Carbon $charge_date_start
     * @param Carbon $charge_date_end
     * @return Collection
     */
    public static function getTargetClosingSupplierData(?string $supplier_id, string $charge_date, string $closing_date,
        Carbon $charge_date_start, Carbon $charge_date_end): Collection
    {
        $closing_ym = DateHelper::changeDateFormat($charge_date, 'Ym');

        return MasterSupplier::when(isset($supplier_id), function ($query) use ($supplier_id) {
            return $query->where('id', $supplier_id);
        })
            ->where('closing_date', $closing_date)
            ->with([
                'charges' => function ($query) use ($closing_ym, $closing_date) {
                    $query->where('closing_ym', $closing_ym)
                        ->where('closing_date', $closing_date);
                },
            ])
            ->with([
                'featureCharges' => function ($query) use ($closing_ym, $closing_date) {
                    $query->where(DB::raw('CONCAT(closing_ym,closing_date)'), '>', $closing_ym . $closing_date);
                },
            ])
            ->with([
                'ClosingSalesOrder' => function ($query) use ($charge_date_start, $charge_date_end) {
                    $query->whereBetween('billing_date', [$charge_date_start, $charge_date_end])
                        ->where('transaction_type_id', TransactionType::ON_ACCOUNT);
                },
            ])
            ->with([
                'ClosingDepositOrder' => function ($query) use ($charge_date_start, $charge_date_end) {
                    $query->whereBetween('order_date', [$charge_date_start, $charge_date_end]);
                },
            ])
            ->with([
                'ClosingPurchasePayment' => function ($query) use ($charge_date_start, $charge_date_end) {
                    $query->whereBetween('order_date', [$charge_date_start, $charge_date_end]);
                },
            ])
            ->when(isset($supplier_id), function ($query) use ($supplier_id) {
                return $query->where('supplier_id', $supplier_id);
            })
            ->get();
    }

    /**
     * 締対象となる仕入先毎の支払伝票情報を取得
     *
     * @param string $supplier_id
     * @param string $start_date
     * @param string $end_date
     * @param int $department_id
     * @param int $office_facilities_id
     * @return array
     */
    public static function getTargetClosingIds(string $supplier_id, string $start_date, string $end_date, int $department_id, int $office_facilities_id): array
    {
        return self::query()
            ->where('transaction_type_id', TransactionType::ON_ACCOUNT)
            ->where('supplier_id', $supplier_id)
            ->where('department_id', $department_id)
            ->where('office_facilities_id', $office_facilities_id)
            ->whereBetween('order_date', [$start_date, $end_date])
            ->whereNull('closing_at')
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * 締対象となる仕入先毎の支払伝票情報を取得
     *
     * @param array $deposit_order_ids
     * @param string $start_date
     * @param string $end_date
     * @return self
     */
    public static function getTargetClosingData(array $deposit_order_ids, string $start_date, string $end_date): self
    {
        return self::query()
            ->whereIn('payments.id', $deposit_order_ids)
            ->leftJoin('payment_details AS detail', function ($join) {
                $join->on('payments.id', '=', 'detail.payment_id');
                $join->whereNull('detail.deleted_at');
            })
            ->whereBetween('order_date', [$start_date, $end_date])
            ->whereNull('closing_at')
            ->get([
                // 支払用(現金/小切手/振込/手形/相殺)
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
        self::whereIn('id', $order_ids)
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
        self::whereIn('id', $order_ids)
            ->update([
                'closing_at' => null,
            ]);
    }
    // endregion static method
}
