<?php

/**
 * 売上伝票モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Sale;

use App\Consts\DB\Receive\OrdersReceivedConst;
use App\Consts\DB\Sale\SalesOrderConst;
use App\Enums\SalesClassification;
use App\Enums\TransactionType;
use App\Helpers\DateHelper;
use App\Helpers\TaxHelper;
use App\Models\Master\MasterBranch;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterHonorificTitle;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterRecipient;
use App\Models\Master\MasterTransactionType;
use App\Models\Master\MasterUser;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TaxType;

/**
 * 売上伝票モデル
 */
class SalesOrder extends Model
{
    /**
     * factory
     */
    use HasFactory;

    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    // region eloquent-scope

    protected $casts = [
        'sales_total' => 'int',
        'sub_total_tax' => 'int',
    ];

    /**
     * 検索条件
     */
    protected array $condition = [
        'transaction_type' => null,
        'order_number' => null,
        'orders_received_number' => null,
        'order_date' => null,
        'billing_date' => null,
        'customer_id' => null,
        'department_id' => null,
        'office_facility_id' => null,
        'branch_id' => null,
        'recipient_id' => null,
    ];

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'order_number',
        'orders_received_number',
        'order_date',
        'billing_date',
        'department_id',
        'office_facilities_id',
        'customer_id',
        'department_id',
        'office_facilities_id',
        'sales_classification_id',
        'billing_customer_id',
        'branch_id',
        'recipient_id',
        'tax_calc_type_id',
        'transaction_type_id',
        'sales_total',
        'discount',
        'sales_total_normal_out',
        'sales_total_reduced_out',
        'sales_total_normal_in',
        'sales_total_reduced_in',
        'sales_total_free',
        'sales_tax_normal_out',
        'sales_tax_reduced_out',
        'sales_tax_normal_in',
        'sales_tax_reduced_in',
        'closing_at',
        'printing_date',
        'memo',
        'link_pos',
        'creator_id',
        'updated_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

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
            ->when(!empty($target_order_number), function ($query) use ($target_order_number) {
                return $query->where('order_number', $target_order_number);
            })
            // 受注番号で絞り込み
            ->when(!empty($target_orders_received_number), function ($query) use ($target_orders_received_number) {
                return $query->where('orders_received_number', $target_orders_received_number);
            })
            // 種別で絞り込み
            ->when($target_transaction_type !== null, function ($query) use ($target_transaction_type) {
                return $query->transactionType($target_transaction_type);
            })
            // 伝票日付で絞り込み
            ->searchOrderDate($target_order_date)
            // 請求日付で絞り込み
            ->searchBillingDate($target_billing_date)
            // 部門IDで絞り込み
            ->when($target_department_id !== null, function ($query) use ($target_department_id) {
                return $query->departmentId($target_department_id);
            })
            // 事業所IDで絞り込み
            ->when($target_office_facility_id !== null, function ($query) use ($target_office_facility_id) {
                return $query->officeFacilityId($target_office_facility_id);
            })
            // 得意先IDで絞り込み
            ->when(!empty($target_customer_id), function ($query) use ($target_customer_id) {
                return $query->customerId($target_customer_id);
            })
            // 支所IDで絞り込み
            ->when(!empty($target_branch_id), function ($query) use ($target_branch_id) {
                return $query->branchId($target_branch_id);
            })
            // 納品先IDで絞り込み
            ->when(!empty($target_recipient_id), function ($query) use ($target_recipient_id) {
                return $query->recipientId($target_recipient_id);
            });
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
    public function scopeOrderDate(Builder $query,
        ?string $target_order_date_start, ?string $target_order_date_end): Builder
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
     * 指定した請求日のレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param string|null $target_billing_date_start
     * @param string|null $target_billing_date_end
     * @return Builder
     */
    public function scopeBillingDate(Builder $query,
        ?string $target_billing_date_start, ?string $target_billing_date_end): Builder
    {
        if ($target_billing_date_start === null && $target_billing_date_end === null) {
            return $query;
        }

        if ($target_billing_date_start !== null && $target_billing_date_end === null) {
            return $query->where('billing_date', '>=', $target_billing_date_start);
        }

        if ($target_billing_date_start === null && $target_billing_date_end !== null) {
            return $query->where('billing_date', '<=', $target_billing_date_end);
        }

        return $query->whereBetween('billing_date', [$target_billing_date_start, $target_billing_date_end]);
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
     * 指定した得意先IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int $target_customer_id
     * @return Builder
     */
    public function scopeCustomerId(Builder $query, int $target_customer_id): Builder
    {
        return $query->where('sales_orders.customer_id', $target_customer_id);
    }

    /**
     * 指定した支所IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int $target_branch_id
     * @return Builder
     */
    public function scopeBranchId(Builder $query, int $target_branch_id): Builder
    {
        return $query->where('sales_orders.branch_id', $target_branch_id);
    }

    /**
     * 指定した納品先IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int $target_recipient_id
     * @return Builder
     */
    public function scopeRecipientId(Builder $query, int $target_recipient_id): Builder
    {
        return $query->where('sales_orders.recipient_id', $target_recipient_id);
    }

    /**
     * 指定した取引種別IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param int $target_transaction_type_id
     * @return Builder
     */
    public function scopeTransactionTypeId(Builder $query, int $target_transaction_type_id): Builder
    {
        return $query->where('transaction_type_id', $target_transaction_type_id);
    }

    /**
     * 締対象となる得意先毎の売上(売掛)明細情報だけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $sales_order_ids
     * @param int $tax_calc_type
     * @param string $start_date
     * @param string $end_date
     * @return Builder
     */
    public function scopeTargetClosingData(Builder $query, array $sales_order_ids, int $tax_calc_type,
        string $start_date, string $end_date): Builder
    {
        return $query
            ->whereIn('sales_orders.id', $sales_order_ids)
            ->leftJoin('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id');
                $join->whereNull('sales_order_details.deleted_at');
            })
            ->where('transaction_type_id', TransactionType::ON_ACCOUNT)
            ->where('tax_calc_type_id', $tax_calc_type)
            ->whereBetween('billing_date', [$start_date, $end_date])
            ->whereNull('closing_at');
    }

    /**
     * 指定した納品先IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $select_column
     * @return Builder
     */
    public function scopeSalesOrderDetailSelectJoin(Builder $query, array $select_column): Builder
    {
        return $query->select($select_column)
            ->join('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->whereNull('sales_order_details.deleted_at');
            });
    }

    /**
     * 売上伝票詳細をJOINするクエリースコープ
     *
     * @param Builder $query
     * @param int $target_product_id
     * @return Builder
     */
    public function scopeSalesOrderDetailJoin(Builder $query, int $target_product_id): Builder
    {
        return $query
            ->leftjoin('sales_order_details', function ($join) use ($target_product_id) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->when(isset($target_product_id), function ($query) use ($target_product_id) {
                        // 商品IDで絞り込み
                        return $query->where('sales_order_details.product_id', $target_product_id);
                    })
                    ->whereNull('sales_order_details.deleted_at');
            });
    }

    /**
     * 指定した日付のレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array|null $target_order_date
     * @return Builder
     */
    public function scopeSearchOrderDate(Builder $query, ?array $target_order_date): Builder
    {
        return $query
            ->when(!empty($target_order_date), function ($query) use ($target_order_date) {
                return $query->orderDate(
                    $target_order_date['start'] ?? null,
                    $target_order_date['end'] ?? null
                );
            });
    }

    /**
     * 指定した請求日付のレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array|null $target_order_date
     * @return Builder
     */
    public function scopeSearchBillingDate(Builder $query, ?array $target_order_date): Builder
    {
        return $query
            ->when(!empty($target_order_date), function ($query) use ($target_order_date) {
                return $query->billingDate(
                    $target_order_date['start'] ?? null,
                    $target_order_date['end'] ?? null
                );
            });
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
     * 取引種別マスター テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mTransactionType(): BelongsTo
    {
        return $this->belongsTo(MasterTransactionType::class, 'transaction_type_id');
    }

    /**
     * 売上伝票詳細 テーブルとのリレーション
     *
     * @return HasMany
     */
    public function salesOrderDetail(): HasMany
    {
        return $this->hasMany(SalesOrderDetail::class, 'sales_order_id');
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
    public function mOfficeFacility(): BelongsTo
    {
        return $this->belongsTo(MasterOfficeFacility::class, 'office_facilities_id');
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
        $length = SalesOrderConst::ORDER_NUMBER_MAX_LENGTH;

        return sprintf("%0{$length}d", $this->order_number);
    }

    /**
     * 受注番号（ゼロ埋め）を取得
     *
     * @return string
     */
    public function getOrdersReceivedNumberZerofillAttribute(): string
    {
        $length = OrdersReceivedConst::ORDER_NUMBER_MAX_LENGTH;

        return sprintf("%0{$length}d", $this->orders_received_number);
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
     * 請求日（「YYYY/MM/DD]」形式）を取得
     *
     * @return string|null
     */
    public function getBillingDateSlashAttribute(): ?string
    {
        if (is_null($this->billing_date)) {
            return null;
        }

        return Carbon::parse($this->billing_date)->format('Y/m/d');
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
     * 得意先コードを取得
     *
     * @return string
     */
    public function getCustomerCodeZeroFillAttribute(): string
    {
        return $this->mCustomer->code_zerofill ?? '';
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
    public function getBillingCustomerIdAttribute(): int
    {
        if (is_null($this->mCustomer->billing_customer_id)) {
            return $this->mCustomer->id;
        }

        return $this->mCustomer->billing_customer_id;
    }

    /**
     * 伝票の消費税額
     *
     * @return float
     */
    public function getTaxAttribute(): float
    {
        $data = self::query()
            ->leftJoin('sales_order_details', function ($join) {
                $join->on('sales_orders.id', '=', 'sales_order_details.sales_order_id');
                $join->whereNull('sales_order_details.deleted_at');
            })
            ->where('sales_orders.id', $this->id)
            ->where('sales_order_details.tax_type_id', TaxType::OUT_TAX)   // 外税対象（内税はいったん除外）
            ->where('sales_order_details.consumption_tax_rate', '>', 0) // 非課税以外(8％/10％)
            ->groupBy(
                [
                    'sales_order_details.tax_type_id',
                    'sales_order_details.consumption_tax_rate',
                    'sales_order_details.reduced_tax_flag',
                    'sales_order_details.rounding_method_id',
                ]
            )
            ->get([
                // \DB::raw('SUM( sales_order_details.unit_price*sales_order_details.quantity ) AS sales_total'),
                \DB::raw('SUM( (sales_order_details.unit_price*sales_order_details.quantity ) -sales_order_details.discount ) AS sales_total'),
                \DB::raw('sales_order_details.tax_type_id'),
                \DB::raw('sales_order_details.consumption_tax_rate'),
                \DB::raw('sales_order_details.reduced_tax_flag'),
                \DB::raw('sales_order_details.rounding_method_id'),
            ]);

        $tax = 0;
        // 集計キー単位のサマリーで税計算して合算する
        foreach ($data as $val) {
            $tax += TaxHelper::getTax($val['sales_total'], $val['consumption_tax_rate'], $val['rounding_method_id']);
        }

        return $tax;
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
        return $this->mOfficeFacility->name ?? '';
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

    /**
     * 次の伝票IDを取得
     *
     * @return int|null
     */
    public function getNextOrderIdAttribute(): ?int
    {
        return SalesOrder::query()
            ->where('id', '>', $this->id)
            ->oldest('id')
            ->value('id');
    }

    /**
     * 前の伝票IDを取得
     *
     * @return int|null
     */
    public function getPreviousOrderIdAttribute(): ?int
    {
        return SalesOrder::query()
            ->where('id', '<', $this->id)
            ->OrderByDesc('id')
            ->value('id');
    }
    // endregion eloquent-accessors

    // region static method

    /**
     * 締対象の得意先を取得
     *
     * @param array $conditions
     * @param Carbon $charge_date_start
     * @param Carbon $charge_date_end
     * @return Collection
     */
    public static function getTargetClosingCustomerData(array $conditions,
        Carbon $charge_date_start, Carbon $charge_date_end): Collection
    {
        $customer_id = $conditions['customer_id'] ?? null;
        $charge_date = $conditions['charge_date'] ?? null;
        $closing_date = $conditions['closing_date'] ?? null;
        $closing_ym = DateHelper::changeDateFormat($charge_date, 'Ym');

        return MasterCustomer::query()
            ->where('closing_date', $closing_date)
            ->with([
                'charges' => fn ($query) => $query->where('closing_ym', $closing_ym)
                    ->where('closing_date', $closing_date)
                    ->where('department_id', $conditions['department_id'])
                    ->where('office_facilities_id', $conditions['office_facility_id']),
                'featureCharges' => fn ($query) => $query->where(DB::raw('CONCAT(closing_ym,closing_date)'), '>', $closing_ym . $closing_date)
                    ->where('department_id', $conditions['department_id'])
                    ->where('office_facilities_id', $conditions['office_facility_id']),
                'ClosingSalesOrder' => fn ($query) => $query->whereBetween('billing_date', [$charge_date_start, $charge_date_end])
                    ->where('department_id', $conditions['department_id'])
                    ->where('office_facilities_id', $conditions['office_facility_id'])
                    ->where('transaction_type_id', TransactionType::ON_ACCOUNT)
                    ->where('sales_classification_id', '<=', SalesClassification::CLASSIFICATION_RETURN),
                'ClosingDepositOrder' => fn ($query) => $query->whereBetween('order_date', [$charge_date_start, $charge_date_end])
                    ->where('department_id', $conditions['department_id'])
                    ->where('office_facilities_id', $conditions['office_facility_id'])
                    ->where('transaction_type_id', TransactionType::ON_ACCOUNT),
            ])
            ->whereHas('SalesOrder', fn ($query) => $query->where('department_id', $conditions['department_id'])
                ->where('office_facilities_id', $conditions['office_facility_id']))
            ->when($customer_id !== null, fn ($query) => $query->where('m_customers.billing_customer_id', $customer_id))
            ->get();
    }

    /**
     * 締対象となる得意先毎の売上(売掛)伝票情報を取得
     *
     * @param string $customer_id
     * @param string $start_date
     * @param string $end_date
     * @param int $department_id
     * @param int $office_facilities_id
     * @return array
     */
    public static function getTargetClosingIds(string $customer_id, string $start_date, string $end_date, int $department_id, int $office_facilities_id): array
    {
        return self::query()
            ->where('transaction_type_id', TransactionType::ON_ACCOUNT)
            ->where('sales_classification_id', '<=', SalesClassification::CLASSIFICATION_RETURN)
            ->where('billing_customer_id', $customer_id)
            ->where('department_id', $department_id)
            ->where('office_facilities_id', $office_facilities_id)
            ->whereBetween('billing_date', [$start_date, $end_date])
            ->whereNull('closing_at')
            ->get()
            ->pluck('id')
            ->toArray();
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

    /**
     * 納品書出力日更新
     *
     * @param Request $request
     */
    public static function updatePrintingDate(Request $request): void
    {
        $sale_order = SalesOrder::query()
            ->where('id', $request->id)
            ->first();
        $updated_at = $sale_order->updated_at;
        $sale_order->printing_date = date('Y/m/d');
        $sale_order->save();
        // todo: $sale_order->timestamps = falseが効かなった為、無理やり実装
        $sale_order->updated_at = $updated_at;
        $sale_order->save();
    }

    /**
     * 納品書出力日が入っているかの判定
     *
     * @return string|null
     */
    public function getPrintingDate(): ?string
    {
        return $this->printing_date;
    }

    /**
     * 一番古い売上日付を取得
     *
     * @return string|null
     */
    public static function getTheOldestDate(): ?string
    {
        return self::query()
            ->min('order_date');
    }

    /**
     * 一番新しい売上日付を取得
     *
     * @return string|null
     */
    public static function getTheLatestDate(): ?string
    {
        return self::query()
            ->max('order_date');
    }

    /**
     * 合計を取得
     *
     * @param string $column
     * @return string
     */
    public function getSumAnyColumnBySalesOrderDetail(string $column): string
    {
        return $this->salesOrderDetail->sum($column);
    }
    // endregion static method

}
