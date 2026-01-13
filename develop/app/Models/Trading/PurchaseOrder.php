<?php

/**
 * 仕入伝票モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Trading;

use App\Consts\DB\Trading\PurchaseOrderConst;
use App\Enums\OrderStatus;
use App\Enums\TaxType;
use App\Enums\TransactionType;
use App\Helpers\DateHelper;
use App\Helpers\TaxHelper;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterSupplier;
use App\Models\Master\MasterUser;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 仕入伝票モデル
 */
class PurchaseOrder extends Model
{
    /**
     * factory
     */
    use HasFactory;

    /**
     * 仕入伝票オブザーバ使用
     */
    use PurchaseOrderObservable;

    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'purchase_orders';

    /**
     * 日付を変形する属性
     *
     * @var array
     */
    protected $dates = [
        /** 見積日付 カラム */
        'estimate_date',
        /** 仕入日付 カラム */
        'order_date',
        /** 納品日付 カラム */
        'delivery_date',
        /** 見積有効期限 カラム */
        'estimate_validity_period',
    ];

    /**
     * 検索条件
     */
    protected array $condition = [
        'id' => null,
        'order_number' => null,
        'transaction_type' => null,
        'estimate_date' => null,
        'order_date' => null,
        'delivery_date' => null,
        'employee_id' => null,
        'supplier_id' => null,
        'department_id' => null,
        'office_facility_id' => null,
        'order_status' => null,
    ];

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'transaction_type_id',
        'order_number',
        'order_date',
        'order_status',
        'supplier_id',
        'department_id',
        'office_facilities_id',
        'tax_calc_type_id',
        'purchase_classification_id',
        'closing_date',
        'department_id',
        'office_facilities_id',
        'transaction_type_id',
        'purchase_classification_id',
        'purchase_total',
        'discount',
        'purchase_total_normal_out',
        'purchase_total_reduced_out',
        'purchase_total_normal_in',
        'purchase_total_reduced_in',
        'purchase_total_free',
        'purchase_tax_normal_out',
        'purchase_tax_reduced_out',
        'purchase_tax_normal_in',
        'purchase_tax_reduced_in',
        'closing_at',
        'memo',
        'link_pos',
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
            // IDで絞り込み
            ->when($target_id !== null, function ($query) use ($target_id) {
                return $query->purchaseOrderId($target_id);
            })
            // 伝票番号で絞り込み
            ->when($target_order_number !== null, function ($query) use ($target_order_number) {
                return $query->where('order_number', $target_order_number);
            })
            // 種別で絞り込み
            ->when($target_transaction_type !== null, function ($query) use ($target_transaction_type) {
                return $query->transactionType($target_transaction_type);
            })
            // 見積日で絞り込み
            ->when($target_estimate_date !== null, function ($query) use ($target_estimate_date) {
                return $query->estimateDate(
                    $target_estimate_date['start'] ?? null,
                    $target_estimate_date['end'] ?? null
                );
            })
            // 仕入日で絞り込み
            ->when($target_order_date !== null, function ($query) use ($target_order_date) {
                return $query->orderDate(
                    $target_order_date['start'] ?? null,
                    $target_order_date['end'] ?? null
                );
            })
            // 納品日で絞り込み
            ->when($target_delivery_date !== null, function ($query) use ($target_delivery_date) {
                return $query->deliveryDate(
                    $target_delivery_date['start'] ?? null,
                    $target_delivery_date['end'] ?? null
                );
            })
            // 担当者IDで絞り込み
            ->when($target_employee_id !== null, function ($query) use ($target_employee_id) {
                return $query->employeeId($target_employee_id);
            })
            // 仕入者IDで絞り込み
            ->when($target_supplier_id !== null, function ($query) use ($target_supplier_id) {
                return $query->supplierId($target_supplier_id);
            })
            // 部門IDで絞り込み
            ->when($target_department_id !== null, function ($query) use ($target_department_id) {
                return $query->departmentId($target_department_id);
            })
            // 事業所IDで絞り込み
            ->when($target_office_facility_id !== null, function ($query) use ($target_office_facility_id) {
                return $query->officeFacilityId($target_office_facility_id);
            })
            // 状態で絞り込み
            ->when($target_order_status !== null, function ($query) use ($target_order_status) {
                return $query->orderStatus($target_order_status);
            });
    }

    /**
     * 指定したIDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int $target_id
     * @return Builder
     */
    public function scopePurchaseOrderId(Builder $query, int $target_id): Builder
    {
        return $query->where('id', $target_id);
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
     * 指定した仕入日付のレコードだけに限定するクエリースコープ(範囲)
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

    /**
     * 指定した仕入者IDのレコードだけに限定するクエリースコープ
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
     * 指定した状態のレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $target_order_status
     * @return Builder
     */
    public function scopeOrderStatus(Builder $query, array $target_order_status): Builder
    {
        if ($target_order_status === []) {
            return $query;
        }

        return $query->whereIn('order_status', $target_order_status);
    }

    /**
     * 仕入伝票詳細をJOINするクエリースコープ
     *
     * @param Builder $query
     * @param int $target_product_id
     * @return Builder
     */
    public function scopePurchaseOrderDetailJoin(Builder $query, int $target_product_id): Builder
    {
        return $query->leftJoin('purchase_order_details', function ($join) use ($target_product_id) {
            $join->on('purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
                ->when(isset($target_product_id), function ($query) use ($target_product_id) {
                    // 商品IDで絞り込み
                    return $query->where('purchase_order_details.product_id', $target_product_id);
                })
                ->whereNull('purchase_order_details.deleted_at');
        });
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
     * m_products テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class, 'product_id');
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
     * ユーザーマスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mUpdated(): BelongsTo
    {
        return $this->belongsTo(MasterUser::class, 'updated_id');
    }

    /**
     * 部門名を取得
     *
     * @return string|null
     */
    public function getDepartmentNameAttribute(): ?string
    {
        return $this->mDepartment->name ?? '';
    }

    /**
     * 事業所名を取得
     *
     * @return string|null
     */
    public function getOfficeFacilitiesNameAttribute(): ?string
    {
        return $this->mOfficeFacilities->name ?? '';
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
     * 仕入伝票詳細 テーブルとのリレーション
     *
     * @return HasMany
     */
    public function purchaseOrderDetail(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'purchase_order_id');
    }

    /**
     * 仕入伝票状態履歴 テーブルとのリレーション
     *
     * @return HasMany
     */
    public function purchaseOrderStatusHistory(): HasMany
    {
        return $this->hasMany(PurchaseOrderStatusHistory::class, 'purchase_order_id')
            ->orderByDesc('created_at');
    }

    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 仕入番号（ゼロ埋め）を取得
     *
     * @return string
     */
    public function getOrderNumberZerofillAttribute(): string
    {
        $length = PurchaseOrderConst::ORDER_NUMBER_MAX_LENGTH;

        return sprintf("%0{$length}d", $this->order_number);
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
     * 仕入先コードを取得
     *
     * @return string
     */
    public function getSupplierCodeZeroFillAttribute(): string
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
     * 仕入先郵便番号を取得
     *
     * @return string
     */
    public function getSupplierPostalCodeAttribute(): string
    {
        $postal_code1 = $this->mSupplier->postal_code1 ?? '';
        $postal_code2 = $this->mSupplier->postal_code2 ?? '';

        $postal_code = '';
        if (!empty($postal_code1) && !empty($postal_code2)) {
            $postal_code = $postal_code1 . '-' . $postal_code2;
        }

        return $postal_code;
    }

    /**
     * 仕入先住所を取得
     *
     * @return string
     */
    public function getSpplireAddressAttribute(): string
    {
        return ($this->mSupplier->address1 ?? '') . ($this->mSupplier->address2 ?? '');
    }

    /**
     * 仕入先電話番号を取得
     *
     * @return string
     */
    public function getSupplierTelNumberAttribute(): string
    {
        return $this->mSupplier->tel_number ?? '';
    }

    /**
     * 仕入先FAX番号を取得
     *
     * @return string
     */
    public function getSupplierFaxNumberAttribute(): string
    {
        return $this->mSupplier->fax_number ?? '';
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
     * 伝票の消費税額
     *
     * @return float
     */
    public function getTaxAttribute(): float
    {
        $data = self::query()
            ->leftJoin('purchase_order_details', function ($join) {
                $join->on('purchase_orders.id', '=', 'purchase_order_details.purchase_order_id');
                $join->whereNull('purchase_order_details.deleted_at');
            })
            ->where('purchase_orders.id', $this->id)
            ->where('purchase_order_details.tax_type_id', TaxType::OUT_TAX)   // 外税対象（内税はいったん除外）
            ->where('purchase_order_details.consumption_tax_rate', '>', 0) // 非課税以外(8％/10％)
            ->groupBy(
                [
                    'purchase_order_details.tax_type_id',
                    'purchase_order_details.consumption_tax_rate',
                    'purchase_order_details.reduced_tax_flag',
                    'purchase_order_details.rounding_method_id',
                ]
            )
            ->get([
                // \DB::raw('SUM( purchase_order_details.unit_price*purchase_order_details.quantity ) AS purchase_total'),
                \DB::raw('SUM( (purchase_order_details.unit_price*purchase_order_details.quantity ) -purchase_order_details.discount ) AS purchase_total'),
                \DB::raw('purchase_order_details.tax_type_id'),
                \DB::raw('purchase_order_details.consumption_tax_rate'),
                \DB::raw('purchase_order_details.reduced_tax_flag'),
                \DB::raw('purchase_order_details.rounding_method_id'),
            ]);

        $tax = 0;
        // 集計キー単位のサマリーで税計算して合算する
        foreach ($data as $val) {
            $tax += TaxHelper::getTax($val['purchase_total'], $val['consumption_tax_rate'], $val['rounding_method_id']);
        }

        return $tax;
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
            ->with(['mSupplier', 'mUpdated'])
            ->orderByDesc('order_number')   // 仕入番号の降順
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.sales_order.page_count'));
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
                DB::raw('SUM( purchase_total ) AS purchase_total'),
                DB::raw('SUM( purchase_tax_normal_out + purchase_tax_reduced_out ) AS purchase_tax_total'),
            ]);
    }

    /**
     * 締対象の仕入先を取得
     *
     * @param array $conditions
     * @param Carbon $charge_date_start
     * @param Carbon $charge_date_end
     * @return Collection
     */
    public static function getTargetClosingSupplierData(array $conditions,
        Carbon $charge_date_start, Carbon $charge_date_end): Collection
    {
        $supplier_id = $conditions['supplier_id'] ?? null;
        $purchase_date = $conditions['purchase_date'] ?? null;
        $closing_date = $conditions['closing_date'] ?? null;
        $closing_ym = DateHelper::changeDateFormat($purchase_date, 'Ym');

        return MasterSupplier::query()
            ->where('m_suppliers.closing_date', $closing_date)
            ->with([
                'charges' => fn ($query) => $query->where('closing_ym', $closing_ym)
                    ->where('closing_date', $closing_date)
                    ->where('department_id', $conditions['department_id'])
                    ->where('office_facilities_id', $conditions['office_facility_id']),
                'featureCharges' => fn ($query) => $query->where(DB::raw('CONCAT(closing_ym,closing_date)'), '>', $closing_ym . $closing_date)
                    ->where('department_id', $conditions['department_id'])
                    ->where('office_facilities_id', $conditions['office_facility_id']),
                'ClosingPurchaseOrder' => fn ($query) => $query->whereBetween('closing_date', [$charge_date_start, $charge_date_end])
                    ->where('department_id', $conditions['department_id'])
                    ->where('office_facilities_id', $conditions['office_facility_id'])
                    ->where('transaction_type_id', TransactionType::ON_ACCOUNT),
                'ClosingPayment' => fn ($query) => $query->whereBetween('order_date', [$charge_date_start, $charge_date_end])
                    ->where('department_id', $conditions['department_id'])
                    ->where('office_facilities_id', $conditions['office_facility_id'])
                    ->where('transaction_type_id', TransactionType::ON_ACCOUNT),
            ])
            ->whereHas('ClosingPurchaseOrder', fn ($query) => $query->where('department_id', $conditions['department_id'])
                ->where('office_facilities_id', $conditions['office_facility_id'])
                ->where('transaction_type_id', TransactionType::ON_ACCOUNT))
            ->when($supplier_id !== null, function ($query) use ($supplier_id) {
                return $query->where('m_suppliers.id', $supplier_id);
            })
            ->get();
    }

    /**
     * 仕入データを取得
     *
     * @param string $supplier_id
     * @param string $purchase_date
     * @param int $closing_date
     * @return Collection
     */
    public static function getPurchaseData(string $supplier_id, string $purchase_date, int $closing_date): Collection
    {
        $str_year = explode('-', $purchase_date)[0];
        $str_month = explode('-', $purchase_date)[1];

        return self::query()
            ->where('supplier_id', $supplier_id)
            ->where('closing_ym', $str_year . $str_month)
            ->where('closing_date', $closing_date)
            ->get();
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

    /**
     * 締対象となる仕入先毎の仕入伝票情報を取得
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
            ->whereBetween('closing_date', [$start_date, $end_date])
            ->whereNull('closing_at')
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * 締対象となる得意先毎の仕入伝票情報を取得
     *
     * @param array $purchase_order_ids
     * @param string $start_date
     * @param string $end_date
     * @return Collection
     */
    public static function getTargetClosingData(array $purchase_order_ids, string $start_date, string $end_date): Collection
    {
        return self::query()
            ->whereIn('purchase_orders.id', $purchase_order_ids)
            ->leftJoin('purchase_order_details', function ($join) {
                $join->on('purchase_orders.id', '=', 'purchase_order_details.purchase_order_id');
                $join->whereNull('purchase_order_details.deleted_at');
            })
            ->whereBetween('closing_date', [$start_date, $end_date])
            ->whereNull('closing_at')
            ->groupBy(
                [
                    'purchase_order_details.tax_type_id',
                    'purchase_order_details.consumption_tax_rate',
                    'purchase_order_details.reduced_tax_flag',
                    'purchase_order_details.rounding_method_id',
                ]
            )
            ->get([
                \DB::raw('SUM( purchase_order_details.sub_total ) AS purchase_total'),
                \DB::raw('SUM( purchase_order_details.sub_total_tax ) AS purchase_total_tax'),
                \DB::raw('purchase_order_details.tax_type_id'),
                \DB::raw('purchase_order_details.consumption_tax_rate'),
                \DB::raw('purchase_order_details.reduced_tax_flag'),
                \DB::raw('purchase_order_details.rounding_method_id'),
            ]);
    }
    // endregion static method
}
