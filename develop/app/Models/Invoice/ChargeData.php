<?php

/**
 * 請求データモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Invoice;

use App\Helpers\DateHelper;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterHonorificTitle;
use App\Models\Master\MasterUser;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 請求データモデル
 */
class ChargeData extends Model
{
    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    protected $fillable = [
        'charge_start_date',
        'charge_end_date',
        'customer_id',
        'closing_ym',
        'closing_date',
        'department_id',
        'office_facilities_id',
        'before_charge_total',
        'payment_total',
        'adjust_amount',
        'carryover',
        'sales_total',
        'sales_total_normal_out',
        'sales_total_reduced_out',
        'sales_total_normal_in',
        'sales_total_reduced_in',
        'sales_total_free',
        'discount_total',
        'sales_tax_total',
        'sales_tax_normal_out',
        'sales_tax_reduced_out',
        'sales_tax_normal_in',
        'sales_tax_reduced_in',
        'charge_total',
        'sales_order_count',
        'deposit_order_count',
        'closing_user_id',
        'closing_at',
    ];

    /**
     * 日付を変形する属性
     *
     * @var array
     */
    protected array $dates = [
        'charge_start_date',
        'charge_end_date',
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
        $closing_ym = DateHelper::changeDateFormat($search_condition_input_data['charge_date'], 'Ym');
        $closing_date = $search_condition_input_data['closing_date'];
        $customer_id = $search_condition_input_data['customer_id'] ?? null;
        $department_id = $search_condition_input_data['department_id'] ?? null;
        $office_facilities_id = $search_condition_input_data['office_facility_id'] ?? null;
        $employee_id = $search_condition_input_data['employee_id'] ?? null;

        return $query
            ->when($closing_ym !== null, function ($query) use ($closing_ym) {
                return $query->where('charge_data.closing_ym', $closing_ym);
            })
            ->when($closing_date !== null, function ($query) use ($closing_date) {
                return $query->where('charge_data.closing_date', $closing_date);
            })
            // 得意先IDで絞り込み
            ->when($customer_id !== null, function ($query) use ($customer_id) {
                return $query->where('charge_data.customer_id', $customer_id);
            })
            // 部門IDで絞り込み
            ->when($department_id !== null, function ($query) use ($department_id) {
                return $query->where('charge_data.department_id', $department_id);
            })
            // 事業所IDで絞り込み
            ->when($office_facilities_id !== null, function ($query) use ($office_facilities_id) {
                return $query->where('charge_data.office_facilities_id', $office_facilities_id);
            })
            // 担当者IDで絞り込み
            ->when($employee_id !== null, function ($query) use ($employee_id) {
                return $query->whereRelation('mCustomer', 'employee_id', $employee_id);
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
        if ($target_charge_date === null) {
            return $query;
        }

        return $query->where('charge_start_date', '<=', $target_charge_date)
            ->where('charge_end_date', '>=', $target_charge_date);
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
        if ($target_charge_date_start === null && $target_charge_date_end === null) {
            return $query;
        }

        if ($target_charge_date_start !== null && $target_charge_date_end === null) {
            return $query->where('charge_start_date', '<=', $target_charge_date_start);
        }

        if ($target_charge_date_start === null && $target_charge_date_end !== null) {
            return $query->where('charge_end_date', '>=', $target_charge_date_end);
        }

        return $query->where('charge_start_date', '<=', $target_charge_date_start)
            ->where('charge_end_date', '>=', $target_charge_date_end);
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
     * 指定した得意先IDのレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param int|null $target_customer_id_start
     * @param int|null $target_customer_id_end
     * @return Builder
     */
    public function scopeCustomerIdRange(Builder $query, ?int $target_customer_id_start, ?int $target_customer_id_end): Builder
    {
        if ($target_customer_id_start === null && $target_customer_id_end === null) {
            return $query;
        }

        if ($target_customer_id_start !== null && $target_customer_id_end === null) {
            return $query->where('customer_id', '>=', $target_customer_id_start);
        }

        if ($target_customer_id_start === null && $target_customer_id_end !== null) {
            return $query->where('customer_id', '<=', $target_customer_id_end);
        }

        return $query->whereBetween('customer_id', [$target_customer_id_start, $target_customer_id_end]);
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
     * ユーザーマスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mUser(): BelongsTo
    {
        return $this->belongsTo(MasterUser::class, 'closing_user_id');
    }

    /**
     * 請求データ_売上伝票 リレーション情報を取得
     *
     * @return HasMany
     */
    public function chargeDataSalesOrder(): HasMany
    {
        return $this->hasMany(ChargeDataSalesOrder::class, 'charge_data_id');
    }

    /**
     * 請求データ_入金伝票 リレーション情報を取得
     *
     * @return HasMany
     */
    public function chargeDataDepositOrder(): HasMany
    {
        return $this->hasMany(ChargeDataDepositOrder::class, 'charge_data_id');
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
        if (is_null($this->charge_start_date)) {
            return null;
        }

        return Carbon::parse($this->charge_start_date)->format('Y/m/d');
    }

    /**
     * 請求終了日（「YYYY/MM/DD」形式）を取得
     *
     * @return string|null
     */
    public function getChargeEndDateSlashAttribute(): ?string
    {
        if (is_null($this->charge_end_date)) {
            return null;
        }

        return Carbon::parse($this->charge_end_date)->format('Y/m/d');
    }

    /**
     * 得意先コードを取得
     *
     * @return string
     */
    public function getCustomerCodeZerofillAttribute(): string
    {
        return $this->mCustomer->code_zerofill ?? '';
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
     * 得意先名+敬称を取得
     *
     * @return string
     */
    public function getCnameHtitleAttribute(): string
    {
        $honorific_title = new MasterHonorificTitle();

        return $this->customer_name . '　' . $honorific_title->name_fixed; // 敬称"様"固定;
    }

    /**
     * 郵便番号１を取得
     *
     * @return string
     */
    public function getCustomerPostalCode1Attribute(): string
    {
        return $this->mCustomer->postal_code1 ?? '';
    }

    /**
     * 郵便番号２を取得
     *
     * @return string
     */
    public function getCustomerPostalCode2Attribute(): string
    {
        return $this->mCustomer->postal_code2 ?? '';
    }

    /**
     * 住所１を取得
     *
     * @return string
     */
    public function getCustomerAddress1Attribute(): string
    {
        return $this->mCustomer->address1 ?? '';
    }

    /**
     * 住所２を取得
     *
     * @return string
     */
    public function getCustomerAddress2Attribute(): string
    {
        return $this->mCustomer->address2 ?? '';
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
     * 今回総売上合計額を取得
     *
     * @return int
     */
    public function getSalesTotalAmountTotalAttribute(): int
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

        $charge_total += $this->before_charge_total ?? 0;   // 前回請求額
        $charge_total -= $this->payment_total ?? 0;         // 今回入金額
        $charge_total -= $this->adjust_amount ?? 0;         // 調整額
        $charge_total += $this->carryover ?? 0;             // 繰越残高
        $charge_total += $this->sales_total ?? 0;           // 今回売上額
        $charge_total += $this->sales_tax_total ?? 0;       // 消費税額

        return $charge_total;
    }

    /**
     * 請求データに紐づく売上伝票IDS
     *
     * @return array
     */
    public function getChargeDataSalesIdsAttribute(): array
    {
        $order_ids = [];
        foreach ($this->chargeDataSalesOrder as $order) {
            $order_ids[] = $order->sales_order_id;
        }

        return $order_ids;
    }

    /**
     * 請求データに紐づく入金伝票IDS
     *
     * @return array
     */
    public function getChargeDataDepositIdsAttribute(): array
    {
        $order_ids = [];
        foreach ($this->chargeDataDepositOrder as $order) {
            $order_ids[] = $order->deposit_order_id;
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

    /**
     * 締実行者を取得
     *
     * @return string|null
     */
    public function getClosingUserNameAttribute(): ?string
    {
        return $this->mUser->name ?? null;
    }

    // endregion eloquent-accessors

    // region static method

    /**
     * 検索結果を取得
     *
     * @param array $search_condition_input_data
     * @return Collection
     */
    public static function getSearchResult(array $search_condition_input_data): Collection
    {
        return self::query()
            ->with(['mCustomer'])
            ->leftJoin('m_customers', 'charge_data.customer_id', '=', 'm_customers.id')
            ->oldest('m_customers.code')   // 得意先コード 昇順
            ->searchCondition($search_condition_input_data)
            ->select('charge_data.*')
            ->get();
    }

    /**
     * 検索結果を取得(ページネーション)
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public static function getSearchResultPaginate(array $search_condition_input_data): LengthAwarePaginator
    {
        return self::query()
            ->with(['mCustomer'])
            ->leftJoin('m_customers', 'charge_data.customer_id', '=', 'm_customers.id')
            ->oldest('m_customers.code')   // 得意先コード 昇順
            ->searchCondition($search_condition_input_data)
            ->select('charge_data.*')
            ->paginate(config('consts.default.charge.page_count'));
    }

    /**
     * 検索結果を取得(合計)
     *
     * @param array $search_condition_input_data
     * @return Collection
     */
    public static function getSearchResultTotal(array $search_condition_input_data): Collection
    {
        return self::query()
            ->searchCondition($search_condition_input_data)
            ->get([
                DB::raw('SUM( before_charge_total ) AS before_charge_total'),
                DB::raw('SUM( payment_total ) AS payment_total'),
                DB::raw('SUM( adjust_amount ) AS adjust_amount_total'),
                DB::raw('SUM( carryover ) AS carryover_total'),
                DB::raw('SUM( sales_total ) AS sales_total'),
                DB::raw('SUM( sales_tax_total ) AS sales_tax_total'),
                DB::raw('SUM( charge_total ) AS charge_total'),
            ]);
    }

    /**
     * 請求データを取得
     *
     * @param string $customer_id
     * @param string $charge_year_month
     * @param int $closing_date
     * @return Collection
     */
    public static function getChargeData(string $customer_id, string $charge_year_month, int $closing_date): Collection
    {
        $str_year = explode('-', $charge_year_month)[0];
        $str_month = explode('-', $charge_year_month)[1];

        return self::query()
            ->where('customer_id', $customer_id)
            ->where('closing_ym', $str_year . $str_month)
            ->where('closing_date', $closing_date)
            ->get();
    }

    /**
     * 前回請求額を取得
     *
     * @param int $customer_id
     * @param string $closing_ym
     * @param int $closing_date
     * @return int
     */
    public static function getBeforeChargeTotal(int $customer_id, string $closing_ym, int $closing_date): int
    {
        return self::query()
            ->where('customer_id', $customer_id)
            ->where(DB::raw('CONCAT(closing_ym,closing_date)'), '<', $closing_ym . $closing_date)
            ->orderByDesc('closing_ym')
            ->firstOrNew([])
            ->charge_total ?? 0;
    }

    /**
     * 直近の請求残高を取得
     *
     * @param array $search_condition_input_data
     * @return ChargeData
     */
    public static function getLastCarryover(array $search_condition_input_data): ?self
    {
        $closing_ym = null;
        if (isset($search_condition_input_data['order_date']['start'])) {
            $closing_ym = DateHelper::changeDateFormat($search_condition_input_data['order_date']['start'], 'Ym');
        }
        $customer_id = $search_condition_input_data['customer_id'] ?? null;

        return self::query()
            ->select('charge_total')
            // 請求締年月
            ->when(!is_null($closing_ym), function ($query) use ($closing_ym) {
                return $query->where('charge_data.closing_ym', '<', $closing_ym);
            })
            // 得意先IDで絞り込み
            ->when(!is_null($customer_id), function ($query) use ($customer_id) {
                return $query->where('charge_data.customer_id', $customer_id);
            })
            ->latest('closing_ym')
            ->first();
    }

    /**
     * 直近の請求残高を取得
     *
     * @param array $search_condition_input_data
     * @return ChargeData
     */
    public static function getPreviousMonthCarryOver(array $search_condition_input_data): ?self
    {
        $closing_ym = null;
        if (isset($search_condition_input_data['order_date']['start'])) {
            $closing_ym = DateHelper::changeDateFormat($search_condition_input_data['order_date']['start'], 'Ym');
        }
        $customer_id = $search_condition_input_data['customer_id'] ?? null;

        return self::query()
            ->select('carryover')
            // 請求締年月
            ->when(!is_null($closing_ym), function ($query) use ($closing_ym) {
                return $query->where('charge_data.closing_ym', '<', $closing_ym);
            })
            // 得意先IDで絞り込み
            ->when(!is_null($customer_id), function ($query) use ($customer_id) {
                return $query->where('charge_data.customer_id', $customer_id);
            })
            ->latest('closing_ym')
            ->first();
    }

    /**
     * 伝票リレーション作成
     *
     * @param array $sales_order_ids
     * @param array $deposit_order_ids
     */
    public function makeOrderRelation(array $sales_order_ids, array $deposit_order_ids): void
    {
        // 請求データ　売上伝票リレーション登録
        $this->createSalesOrderRelation($sales_order_ids);
        // 入金データ　入金伝票リレーション登録
        $this->createDepositOrderRelation($deposit_order_ids);
    }

    /**
     * 売上伝票 リレーション情報登録
     *
     * @param array $order_ids
     */
    public function createSalesOrderRelation(array $order_ids): void
    {
        $details = [];
        foreach ($order_ids as $sales_order_id) {
            $details[] = ['sales_order_id' => $sales_order_id];
        }
        $this->chargeDataSalesOrder()->createMany($details);
    }

    /**
     * 入金伝票 リレーション情報登録
     *
     * @param array $order_ids
     */
    public function createDepositOrderRelation(array $order_ids): void
    {
        $details = [];
        foreach ($order_ids as $deposit_order_id) {
            $details[] = ['deposit_order_id' => $deposit_order_id];
        }
        $this->chargeDataDepositOrder()->createMany($details);
    }
    // endregion static method
}
