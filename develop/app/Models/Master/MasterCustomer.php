<?php

/**
 * 得意先マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use App\Consts\DB\Master\MasterCustomersConst;
use App\Helpers\MasterIntegrityHelper;
use App\Models\Invoice\ChargeData;
use App\Models\Sale\DepositOrder;
use App\Models\Sale\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 得意先マスターモデル
 */
class MasterCustomer extends Model
{
    /**
     * コード用トレイト使用
     */
    use HasCode;

    /**
     * 名前かな用トレイト使用
     */
    use HasNameKana;

    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'm_customers';

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'name_kana',
        'postal_code1',
        'postal_code2',
        'address1',
        'address2',
        'tel_number',
        'fax_number',
        'email',
        'billing_customer_id',
        'tax_calc_type_id',
        'tax_rounding_method_id',
        'transaction_type_id',
        'closing_date',
        'start_account_receivable_balance',
        'sort_code',
        'billing_balance',
        'collection_month',
        'collection_day',
        'collection_method',
        'sales_invoice_format_type',
        'sales_invoice_printing_method',
        'employee_id',
        'summary_group_id',
        'department_id',
        'office_facilities_id',
        'note',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // コード値の桁数セット
        $this->code_length = MasterCustomersConst::CODE_MAX_LENGTH;
    }

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
        $target_id = $search_condition_input_data['id'] ?? null;
        $target_code = $search_condition_input_data['code'] ?? null;
        $target_name = $search_condition_input_data['name'] ?? null;
        $closing_date = $search_condition_input_data['closing_date'] ?? null;

        return $query
            ->when($target_id !== null, function ($query) use ($target_id) {
                return $query->id($target_id['start'] ?? null, $target_id['end'] ?? null);
            })
            ->when($target_code !== null, function ($query) use ($target_code) {
                return $query->code($target_code['start'] ?? null, $target_code['end'] ?? null);
            })
            ->when($target_name !== null, function ($query) use ($target_name) {
                return $query->name($target_name);
            })
            ->when($closing_date !== null, function ($query) use ($closing_date) {
                return $query->whereIn('closing_date', $closing_date);
            })
            // コード値昇順
            ->oldest('code');
    }

    /**
     * 指定したIDのレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param int|null $target_id_start
     * @param int|null $target_id_end
     * @return Builder
     */
    public function scopeId(Builder $query, ?int $target_id_start, ?int $target_id_end): Builder
    {
        if ($target_id_start === null && $target_id_end === null) {
            return $query;
        }
        if ($target_id_start !== null && $target_id_end === null) {
            return $query->where('id', '>=', $target_id_start);
        }
        if ($target_id_start === null && $target_id_end !== null) {
            return $query->where('id', '<=', $target_id_end);
        }

        return $query->whereBetween('id', [$target_id_start, $target_id_end]);
    }

    /**
     * 指定した名前のレコードだけに限定するクエリースコープ(部分一致)
     *
     * @param Builder $query
     * @param string $target_name
     * @return Builder
     */
    public function scopeName(Builder $query, string $target_name): Builder
    {
        return $query->where('name', 'LIKE', '%' . $target_name . '%');
    }

    /**
     * フリーワード検索
     *
     * @param Builder $query
     * @param string $free_word
     * @return Builder
     */
    public function scopeFreeWord(Builder $query, string $free_word): Builder
    {
        return $query
            ->where('name', 'LIKE', '%' . $free_word . '%');
    }
    // endregion eloquent-scope

    // region eloquent-relationships

    /**
     * 得意先_敬称 リレーション情報を取得
     *
     * @return HasOne
     */
    public function mCustomerHonorificTitle(): HasOne
    {
        return $this->hasOne(MasterCustomerHonorificTitle::class, 'customer_id');
    }

    /**
     * 請求先情報を取得
     *
     * @return HasOne
     */
    public function mBillingCustomer(): HasOne
    {
        return $this->hasOne(MasterCustomer::class, 'id', 'billing_customer_id');
    }

    /**
     * 部門 リレーション情報を取得
     *
     * @return HasOne
     */
    public function mDepartment(): HasOne
    {
        return $this->hasOne(MasterDepartment::class, 'id', 'department_id');
    }

    /**
     * 事業所 リレーション情報を取得
     *
     * @return HasOne
     */
    public function mOfficeFacility(): HasOne
    {
        return $this->hasOne(MasterOfficeFacility::class, 'id', 'office_facilities_id');
    }

    /**
     * 販売情報 リレーション情報を取得
     *
     * @return HasMany
     */
    public function SalesOrder(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'customer_id');
    }

    /**
     * 入金情報 リレーション情報を取得
     *
     * @return HasMany
     */
    public function DepositOrder(): HasMany
    {
        return $this->hasMany(DepositOrder::class, 'customer_id');
    }

    /**
     * 請求締情報を取得
     *
     * @return HasMany
     */
    public function charges(): HasMany
    {
        return $this->hasMany(ChargeData::class, 'customer_id', 'billing_customer_id')
            ->with('mUser');
    }

    /**
     * 未来の締済み請求締情報を取得
     *
     * @return HasMany
     */
    public function featureCharges(): HasMany
    {
        return $this->hasMany(ChargeData::class, 'customer_id', 'billing_customer_id');
    }

    /**
     * 販売情報 リレーション情報を取得
     *
     * @return HasMany
     */
    public function ClosingSalesOrder(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'billing_customer_id', 'billing_customer_id');
    }

    /**
     * 入金情報 リレーション情報を取得
     *
     * @return HasMany
     */
    public function ClosingDepositOrder(): HasMany
    {
        return $this->hasMany(DepositOrder::class, 'billing_customer_id', 'billing_customer_id');
    }

    /**
     * 請求締情報を取得
     *
     * @return HasMany
     */
    public function ClosingCharges(): HasMany
    {
        return $this->hasMany(ChargeData::class, 'customer_id', 'billing_customer_id');
    }

    /**
     * 支所 リレーション情報を取得
     *
     * @return HasMany
     */
    public function mBranches(): HasMany
    {
        return $this->hasMany(MasterBranch::class, 'customer_id');
    }
    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 担当者 リレーション情報を取得
     *
     * @return belongsTo
     */
    public function employee(): belongsTo
    {
        return $this->belongsTo(MasterEmployee::class, 'employee_id');
    }

    /**
     * 得意先に紐づく敬称を取得
     *
     * @return string|null
     */
    public function getCustomerHonorificTitleNameAttribute(): ?string
    {
        return $this->mCustomerHonorificTitle->honorific_title_name ?? null;
    }

    // endregion eloquent-accessors

    // region eloquent-accessors

    /**
     * 住所1+住所2を取得
     *
     * @return string 住所
     */
    public function getAddressAttribute(): string
    {
        return ($this->address1 ?? '') . ($this->address2 ?? '');
    }

    /**
     * 部門名を取得
     *
     * @return string
     */
    public function getDepartmentNameAttribute(): string
    {
        return $this->mDepartment->name ?? '';
    }

    /**
     * 事業所名を取得
     *
     * @return string
     */
    public function getOfficeFacilityNameAttribute(): string
    {
        return $this->mOfficeFacility->name ?? '';
    }

    // endregion eloquent-accessors

    // region static method

    /**
     * 検索結果を取得 (ページネーション)
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public static function getSearchResultPagenate(array $search_condition_input_data): LengthAwarePaginator
    {
        return self::query()
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.master.customers.page_count'));
    }

    /**
     * 検索結果を取得
     *
     * @param array $search_condition_input_data
     * @return Collection
     */
    public static function getSearchResult(array $search_condition_input_data): Collection
    {
        return self::query()
            ->searchCondition($search_condition_input_data)
            ->get();
    }

    /**
     * 請求先データを取得
     *
     * @param int|null $exclude_customer_id 除外する得意先ID
     * @return Collection
     */
    public static function getBillingCustomer(?int $exclude_customer_id = null): Collection
    {
        return self::query()
            ->when($exclude_customer_id !== null, function ($query) use ($exclude_customer_id) {
                return $query->where('id', '<>', $exclude_customer_id);
            })
            ->oldest('code')
            ->get();
    }

    /**
     * 請求締対象の請求先データを取得
     *
     * @return Collection
     */
    public static function getClosingBillingCustomer(): Collection
    {
        $billing_customer_ids = MasterCustomer::select(DB::raw('COALESCE(billing_customer_id, id) AS id'))
            ->groupBy(DB::raw('COALESCE(billing_customer_id, id)'))->pluck('id')->toarray();

        return self::query()
            ->whereIn('id', $billing_customer_ids)
            ->oldest('code')
            ->get();
    }

    /**
     * POS連携用の得意先データを取得
     *
     * @param string $target_date
     * @return Collection
     */
    public static function getCustomerDataByPos(string $target_date, string $limit_count): Collection
    {
        return self::query()
            ->select([
                'm_customers.code AS customer_code',
                'm_customers.name AS customer_name',
                'm_customers.name_kana AS customer_name_kana',
                'tel_number',
                DB::raw("CONCAT(m_customers.postal_code1, '-', m_customers.postal_code2) AS postal_code"),
                'address1',
                'address2',
                DB::raw("CASE
                    WHEN m_customers.deleted_at IS NULL OR CAST(m_customers.deleted_at AS CHAR) = '' THEN 0
                    ELSE 1 END AS del_flg"),
                DB::raw("DATE_FORMAT(m_customers.updated_at, '%Y/%m/%d') AS updated_date"),
                DB::raw("DATE_FORMAT(m_customers.updated_at, '%H:%i:%s') AS updated_time"),
                // TODO: 対応待ち
                DB::raw('0000 AS store_code'),
                // TODO: 対応待ち
                DB::raw('1 AS tax_class'),
            ])
            ->where('m_customers.updated_at', '>=', $target_date)
            ->orderBy('m_customers.updated_at')
            ->orderBy('m_customers.code')
            ->limit($limit_count)
            ->get();
    }

    /**
     * マスタの使用状況を返す
     *
     * @return bool
     */
    public function getUseMasterAttribute(): bool
    {
        return MasterIntegrityHelper::existsUseMasterCustomer($this->id);
    }

    /**
     * IDリストを取得（IDの配列取得）
     *
     * @return array
     */
    public static function getIdList(): array
    {
        return self::query()
            ->get()
            ->pluck('id')
            ->all() ?? [];
    }

    // endregion static method
}
