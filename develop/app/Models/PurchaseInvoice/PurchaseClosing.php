<?php

/**
 * 請求データモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\PurchaseInvoice;

use App\Helpers\DateHelper;
use App\Models\Master\MasterSupplier;
use App\Models\Master\MasterUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 仕入締データモデル
 */
class PurchaseClosing extends Model
{
    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    protected $fillable = [
        'purchase_closing_start_date',
        'purchase_closing_end_date',
        'supplier_id',
        'closing_ym',
        'closing_date',
        'department_id',
        'office_facilities_id',
        'before_purchase_total',
        'payment_total',
        'adjust_amount',
        'carryover',
        'purchase_total',
        'purchase_total_normal_out',
        'purchase_total_reduced_out',
        'purchase_total_normal_in',
        'purchase_total_reduced_in',
        'purchase_total_free',
        'discount_total',
        'purchase_tax_total',
        'purchase_tax_normal_out',
        'purchase_tax_reduced_out',
        'purchase_tax_normal_in',
        'purchase_tax_reduced_in',
        'purchase_closing_total',
        'purchase_order_count',
        'payment_count',
        'closing_user_id',
        'closing_at',
    ];

    /**
     * 日付を変形する属性
     *
     * @var array
     */
    protected $dates = [
        'purchase_closing_start_date',
        'purchase_closing_end_date',
    ];

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'purchase_closing';

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
        $closing_ym = DateHelper::changeDateFormat($search_condition_input_data['purchase_date'], 'Ym');
        $closing_date = $search_condition_input_data['closing_date'];
        $supplier_id = $search_condition_input_data['supplier_id'] ?? null;
        $department_id = $search_condition_input_data['department_id'] ?? null;
        $office_facilities_id = $search_condition_input_data['office_facility_id'] ?? null;

        return $query
            ->when(isset($closing_ym), function ($query) use ($closing_ym) {
                return $query->where('purchase_closing.closing_ym', $closing_ym);
            })
            ->when(isset($closing_date), function ($query) use ($closing_date) {
                return $query->where('purchase_closing.closing_date', $closing_date);
            })
            // 仕入先IDで絞り込み
            ->when(isset($supplier_id), function ($query) use ($supplier_id) {
                return $query->where('purchase_closing.supplier_id', $supplier_id);
            })
            // 部門IDで絞り込み
            ->when($department_id !== null, function ($query) use ($department_id) {
                return $query->where('purchase_closing.department_id', $department_id);
            })
            // 事業所IDで絞り込み
            ->when($office_facilities_id !== null, function ($query) use ($office_facilities_id) {
                return $query->where('purchase_closing.office_facilities_id', $office_facilities_id);
            });
    }

    /**
     * 指定した請求日付のレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param string|null $target_charge_date
     * @return Builder
     */
    public function scopeChargeDate(Builder $query, ?string $target_charge_date): Builder
    {
        if (is_null($target_charge_date)) {
            return $query;
        }

        return $query->where('purchase_closing_start_date', '<=', $target_charge_date)
            ->where('purchase_closing_end_date', '>=', $target_charge_date);
    }

    /**
     * 指定した請求日付のレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param string|null $target_charge_date_start
     * @param string|null $target_charge_date_end
     * @return Builder
     */
    public function scopeChargeDateRange(Builder $query, ?string $target_charge_date_start,
        ?string $target_charge_date_end): Builder
    {
        if (is_null($target_charge_date_start) && is_null($target_charge_date_end)) {
            return $query;
        }

        if (isset($target_charge_date_start) && is_null($target_charge_date_end)) {
            return $query->where('purchase_closing_start_date', '<=', $target_charge_date_start);
        }

        if (is_null($target_charge_date_start) && isset($target_charge_date_end)) {
            return $query->where('purchase_closing_end_date', '>=', $target_charge_date_end);
        }

        return $query->where('purchase_closing_start_date', '<=', $target_charge_date_start)
            ->where('purchase_closing_end_date', '>=', $target_charge_date_end);
    }

    /**
     * 指定した得意先IDのレコードだけに限定するクエリースコープ
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
     * 指定した得意先IDのレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param int|null $target_supplier_id_start
     * @param int|null $target_supplier_id_end
     * @return Builder
     */
    public function scopeSupplierIdRange(Builder $query, ?int $target_supplier_id_start, ?int $target_supplier_id_end): Builder
    {
        if (is_null($target_supplier_id_start) && is_null($target_supplier_id_end)) {
            return $query;
        }

        if (isset($target_supplier_id_start) && is_null($target_supplier_id_end)) {
            return $query->where('supplier_id', '>=', $target_supplier_id_start);
        }

        if (is_null($target_supplier_id_start) && isset($target_supplier_id_end)) {
            return $query->where('supplier_id', '<=', $target_supplier_id_end);
        }

        return $query->whereBetween('supplier_id', [$target_supplier_id_start, $target_supplier_id_end]);
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
     * ユーザーマスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mUser(): BelongsTo
    {
        return $this->belongsTo(MasterUser::class, 'closing_user_id');
    }

    /**
     * 仕入締データ_仕入伝票 リレーション情報を取得
     *
     * @return HasMany
     */
    public function purchaseClosingPurchaseOrder(): HasMany
    {
        return $this->hasMany(PurchaseClosingPurchaseOrder::class, 'purchase_closing_id');
    }

    /**
     * 仕入締データ_支払伝票 リレーション情報を取得
     *
     * @return HasMany
     */
    public function purchaseClosingPayment(): HasMany
    {
        return $this->hasMany(PurchaseClosingPayment::class, 'purchase_closing_id');
    }

    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 請求開始日（「YYYY/MM/DD」形式）を取得
     *
     * @return string|null
     */
    public function getChargeStartDateSlashAttribute(): ?string
    {
        if (is_null($this->purchase_closing_start_date)) {
            return null;
        }

        return Carbon::parse($this->purchase_closing_start_date)->format('Y/m/d');
    }

    /**
     * 請求終了日（「YYYY/MM/DD」形式）を取得
     *
     * @return string|null
     */
    public function getChargeEndDateSlashAttribute(): ?string
    {
        if (is_null($this->purchase_closing_end_date)) {
            return null;
        }

        return Carbon::parse($this->purchase_closing_end_date)->format('Y/m/d');
    }

    /**
     * 仕入先コードを取得
     *
     * @return string
     */
    public function getSupplierCodeZerofillAttribute(): string
    {
        return $this->mSupplier->code_zerofill ?? '';
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
     * 郵便番号１を取得
     *
     * @return string
     */
    public function getSupplierPostalCode1Attribute(): string
    {
        return $this->mSupplier->postal_code1 ?? '';
    }

    /**
     * 郵便番号２を取得
     *
     * @return string
     */
    public function getSupplierPostalCode2Attribute(): string
    {
        return $this->mSupplier->postal_code2 ?? '';
    }

    /**
     * 住所１を取得
     *
     * @return string
     */
    public function getSupplierAddress1Attribute(): string
    {
        return $this->mSupplier->address1 ?? '';
    }

    /**
     * 住所２を取得
     *
     * @return string
     */
    public function getSupplierAddress2Attribute(): string
    {
        return $this->mSupplier->address2 ?? '';
    }

    /**
     * 今回総売上額を取得
     *
     * @return int
     */
    public function getSalesTotalAmountAttribute(): int
    {
        $amount = 0;

        $amount += $this->sales_total ?? 0;      // 今回売上額
        $amount += $this->sales_tax_total ?? 0;  // 消費税額

        return $amount;
    }

    /**
     * 計算された今回請求額を取得
     *
     * @return int|null
     */
    public function getCalculatedChargeTotalAttribute(): ?int
    {
        $charge_total = 0;

        $charge_total += $this->before_purchase_total ?? 0;   // 前回請求額
        $charge_total -= $this->payment_total ?? 0;         // 今回入金額
        $charge_total -= $this->adjust_amount ?? 0;         // 調整額
        $charge_total += $this->carryover ?? 0;             // 繰越残高
        $charge_total += $this->sales_total ?? 0;           // 今回売上額
        $charge_total += $this->sales_tax_total ?? 0;       // 消費税額

        return $charge_total;
    }

    /**
     * 仕入締データに紐づく仕入伝票IDS
     *
     * @return array
     */
    public function getPurchaseClosingPurchaseIdsAttribute(): array
    {
        $order_ids = [];
        foreach ($this->purchaseClosingPurchaseOrder as $order) {
            $order_ids[] = $order->purchase_order_id;
        }

        return $order_ids;
    }

    /**
     * 仕入締データに紐づく支払伝票IDS
     *
     * @return array
     */
    public function getPurchaseClosingPaymentIdsAttribute(): array
    {
        $order_ids = [];
        foreach ($this->purchaseClosingPayment as $order) {
            $order_ids[] = $order->payment_id;
        }

        return $order_ids;
    }

    /**
     * 入金予定日（「YYYY/MM/DD]」形式）を取得
     *
     * @return string|null
     */
    public function getPlannedDepositAtSlashAttribute(): ?string
    {
        if (is_null($this->planned_deposit_at)) {
            return null;
        }

        return Carbon::parse($this->planned_deposit_at)->format('Y/m/d');
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
            ->with(['mSupplier'])
            ->leftJoin('m_suppliers', 'purchase_closing.supplier_id', '=', 'm_suppliers.id')
            ->searchCondition($search_condition_input_data)
            ->oldest('m_suppliers.code')   // 得意先コード 昇順
            ->select('purchase_closing.*')
            ->paginate(config('consts.default.charge.page_count'));
    }

    public static function getPurchaseClosingListResult(array $search_condition_input_data): Collection
    {
        return self::query()
            ->with(['mSupplier'])
            ->leftJoin('m_suppliers', 'purchase_closing.supplier_id', '=', 'm_suppliers.id')
            ->searchCondition($search_condition_input_data)
            ->oldest('m_suppliers.code')   // 得意先コード 昇順
            ->select('purchase_closing.*')
            ->get();
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
            ->searchCondition($search_condition_input_data)
            ->get([
                DB::raw('SUM(before_purchase_total) AS before_purchase_total'),
                DB::raw('SUM(payment_total) AS payment_total'),
                DB::raw('SUM(carryover) AS carryover_total'),
                DB::raw('SUM(purchase_total) AS purchase_total'),
                DB::raw('SUM(purchase_tax_total) AS purchase_tax_total'),
                DB::raw('SUM(purchase_closing_total) AS purchase_closing_total'),
            ]);
    }

    /**
     * 請求データを取得
     *
     * @param string $supplier_id
     * @param string $charge_year_month
     * @param int $closing_date
     * @return Collection
     */
    public static function getChargeData(string $supplier_id, string $charge_year_month, int $closing_date): Collection
    {
        $str_year = explode('-', $charge_year_month)[0];
        $str_month = explode('-', $charge_year_month)[1];

        return self::query()
            ->where('supplier_id', $supplier_id)
            ->where('closing_ym', $str_year . $str_month)
            ->where('closing_date', $closing_date)
            ->get();
    }

    /**
     * 前回請求額を取得
     * ]
     *
     * @param int $supplier_id
     * @param string $closing_ym
     * @param int $closing_date
     * @return int
     */
    public static function getBeforePurchaseTotal(int $supplier_id, string $closing_ym, int $closing_date): int
    {
        return self::query()
            ->where('supplier_id', $supplier_id)
            ->where(DB::raw('CONCAT(closing_ym,closing_date)'), '<', $closing_ym . $closing_date)
            ->orderByDesc('closing_ym')
            ->firstOrNew([])
            ->purchase_closing_total ?? 0;
    }

    /**
     * 直近の繰越残高を取得
     *
     * @param array $search_condition_input_data
     * @return PurchaseClosing
     */
    public static function getPreviousMonthCarryOver(array $search_condition_input_data): ?self
    {
        $closing_ym = DateHelper::changeDateFormat($search_condition_input_data['order_date']['start'], 'Ym');
        $supplier_id = $search_condition_input_data['supplier_id'] ?? null;

        return self::query()
            ->select('carryover')
            // 請求締年月
            ->when($closing_ym, function ($query) use ($closing_ym) {
                return $query->where('purchase_closing.closing_ym', '<', $closing_ym);
            })
            // 仕入先IDで絞り込み
            ->when($supplier_id, function ($query) use ($supplier_id) {
                return $query->where('purchase_closing.supplier_id', $supplier_id);
            })
            ->latest('closing_ym')
            ->first();
    }
    // endregion static method
}
