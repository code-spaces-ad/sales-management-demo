<?php

/**
 * 仕入先マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use App\Consts\DB\Master\MasterSuppliersConst;
use App\Helpers\MasterIntegrityHelper;
use App\Models\PurchaseInvoice\PurchaseClosing;
use App\Models\Trading\Payment;
use App\Models\Trading\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 仕入先マスターモデル
 */
class MasterSupplier extends Model
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
    protected $table = 'm_suppliers';

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
        $this->code_length = MasterSuppliersConst::CODE_MAX_LENGTH;
    }

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
        'supplier_id',
        'tax_calc_type_id',
        'tax_rounding_method_id',
        'transaction_type_id',
        'closing_date',
        'start_account_receivable_balance',
        'billing_balance',
        'collection_month',
        'collection_day',
        'collection_method',
        'sales_invoice_format_type',
        'sales_invoice_printing_method',
        'note',
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
     * 支払先情報を取得
     *
     * @return HasOne
     */
    public function mSupplier(): HasOne
    {
        return $this->hasOne(MasterSupplier::class, 'id', 'supplier_id');
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
     * 仕入締情報を取得
     *
     * @return HasMany
     */
    public function charges(): HasMany
    {
        return $this->hasMany(PurchaseClosing::class, 'supplier_id');
    }

    /**
     * 未来の締済み仕入締情報を取得
     *
     * @return HasMany
     */
    public function featureCharges(): HasMany
    {
        return $this->hasMany(PurchaseClosing::class, 'supplier_id');
    }

    /**
     * 仕入情報 リレーション情報を取得
     *
     * @return HasMany
     */
    public function ClosingPurchaseOrder(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    /**
     * 支払情報 リレーション情報を取得
     *
     * @return HasMany
     */
    public function ClosingPayment(): HasMany
    {
        return $this->hasMany(Payment::class, 'supplier_id');
    }

    /**
     * 仕入締情報を取得
     *
     * @return HasMany
     */
    public function ClosingCharges(): HasMany
    {
        return $this->hasMany(PurchaseClosing::class, 'supplier_id');
    }
    // endregion eloquent-relationships

    // region eloquent-accessors

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
     * 検索結果を取得（ページネーション）
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public static function getSearchResultPagenate(array $search_condition_input_data): LengthAwarePaginator
    {
        return self::query()
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.master.Suppliers.page_count'));
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
     * 仕入先データを取得
     *
     * @param int|null $exclude_Supplier_id 除外する仕入先ID
     * @return Collection
     */
    public static function getBillingSupplier(?int $exclude_supplier_id = null): Collection
    {
        return self::query()
            ->when($exclude_supplier_id !== null, function ($query) use ($exclude_supplier_id) {
                return $query->where('id', '<>', $exclude_supplier_id);
            })
            ->oldest('code')
            ->get();
    }

    /**
     * 仕入締対象の仕入先データを取得
     *
     * @return Collection
     */
    public static function getClosingBillingSupplier(): Collection
    {
        $billing_supplier_ids = MasterSupplier::select(DB::raw('COALESCE(supplier_id, id) AS id'))
            ->groupBy(DB::raw('COALESCE(supplier_id, id)'))->pluck('id')->toarray();

        return self::query()
            ->whereIn('id', $billing_supplier_ids)
            ->oldest('code')
            ->get();
    }

    /**
     * マスタの使用状況を返す
     *
     * @return bool
     */
    public function getUseMasterAttribute(): bool
    {
        return MasterIntegrityHelper::existsUseMasterSupplier($this->id);
    }

    // endregion static method
}
