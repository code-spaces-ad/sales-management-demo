<?php

/**
 * 商品マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use App\Consts\DB\Master\MasterProductsConst;
use App\Enums\SortTypes;
use App\Helpers\MasterIntegrityHelper;
use App\Models\Sale\SalesOrderDetail;
use App\Models\Trading\PurchaseOrderDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 商品マスターモデル
 */
class MasterProduct extends Model
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
    protected $table = 'm_products';

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
        $this->code_length = MasterProductsConst::CODE_MAX_LENGTH;
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
        $target_category_id = $search_condition_input_data['category_id'] ?? null;
        $target_sub_category_id = $search_condition_input_data['sub_category_id'] ?? null;
        $target_customer_product_code = $search_condition_input_data['customer_product_code'] ?? null;
        $target_kind_id = $search_condition_input_data['kind_id'] ?? null;
        $target_classification1_id = $search_condition_input_data['classification1_id'] ?? null;
        $target_classification2_id = $search_condition_input_data['classification2_id'] ?? null;
        $target_reduced_tax_flag = $search_condition_input_data['reduced_tax_flag'] ?? null;

        $category = MasterCategory::query()->select([
            /** カテゴリーID */
            DB::raw('id AS category_id'),
            /** カテゴリー名 */
            DB::raw('name AS category_name'),
        ])->toSql();
        $sub_category = MasterSubCategory::query()->select([
            /** サブカテゴリーID */
            DB::raw('id AS sub_category_id'),
            /** サブカテゴリー名 */
            DB::raw('name AS sub_category_name'),
        ])->toSql();

        return $query
            ->leftJoinSub($category, 'category', 'm_products.category_id', 'category.category_id')
            ->leftJoinSub($sub_category, 'sub_category', 'm_products.sub_category_id', 'sub_category.sub_category_id')
            ->when(isset($target_id), function ($query) use ($target_id) {
                return $query->id($target_id['start'] ?? null, $target_id['end'] ?? null);
            })
            ->when(isset($target_code), function ($query) use ($target_code) {
                return $query->code($target_code['start'] ?? null, $target_code['end'] ?? null);
            })
            ->when(isset($target_name), function ($query) use ($target_name) {
                return $query->name($target_name);
            })
            ->when(isset($target_category_id), function ($query) use ($target_category_id) {
                return $query->where('category.category_id', $target_category_id);
            })
            ->when(isset($target_sub_category_id), function ($query) use ($target_sub_category_id) {
                return $query->where('sub_category.sub_category_id', $target_sub_category_id);
            })
            ->when(isset($target_customer_product_code), function ($query) use ($target_customer_product_code) {
                return $query->where('customer_product_code', 'LIKE', '%' . $target_customer_product_code . '%');
            })
            ->when(isset($target_kind_id), function ($query) use ($target_kind_id) {
                return $query->where('kind_id', $target_kind_id);
            })
            ->when(isset($target_classification1_id), function ($query) use ($target_classification1_id) {
                return $query->where('classification1_id', $target_classification1_id);
            })
            ->when(isset($target_classification2_id), function ($query) use ($target_classification2_id) {
                return $query->where('classification2_id', $target_classification2_id);
            })
            ->when(isset($target_reduced_tax_flag), function ($query) use ($target_reduced_tax_flag) {
                return $query->whereIn('reduced_tax_flag', $target_reduced_tax_flag);
            })
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

    // endregion eloquent-scope

    // region eloquent-relationships

    /**
     * 商品_単位 リレーション情報を取得
     *
     * @return HasOne
     */
    public function mProductUnit(): HasOne
    {
        return $this->hasOne(MasterProductUnit::class, 'product_id');
    }

    /**
     * カテゴリー リレーション情報を取得
     *
     * @return HasOne
     */
    public function mCategory(): HasOne
    {
        return $this->hasOne(MasterCategory::class, 'id', 'category_id');
    }

    /**
     * サブカテゴリー リレーション情報を取得
     *
     * @return HasOne
     */
    public function mSubCategory(): HasOne
    {
        return $this->hasOne(MasterSubCategory::class, 'id', 'sub_category_id');
    }

    /**
     * サブカテゴリー リレーション情報を取得
     *
     * @return HasOne
     */
    public function mKind(): HasOne
    {
        return $this->hasOne(MasterKind::class, 'id', 'kind_id');
    }

    /**
     * 管理部署 リレーション情報を取得
     *
     * @return HasOne
     */
    public function mSection(): HasOne
    {
        return $this->hasOne(MasterSection::class, 'id', 'section_id');
    }

    /**
     * 分類１ リレーション情報を取得
     *
     * @return HasOne
     */
    public function mClassification1(): HasOne
    {
        return $this->hasOne(MasterClassification1::class, 'id', 'classification1_id');
    }

    /**
     * 分類２ リレーション情報を取得
     *
     * @return HasOne
     */
    public function mClassification2(): HasOne
    {
        return $this->hasOne(MasterClassification2::class, 'id', 'classification2_id');
    }

    /**
     * カテゴリー リレーション情報を取得
     *
     * @return HasOne
     */
    public function mSupplier(): HasOne
    {
        return $this->hasOne(MasterSupplier::class, 'id', 'supplier_id');
    }

    /**
     * 経理コード リレーション情報を取得
     *
     * @return HasOne
     */
    public function mAccountingCode(): HasOne
    {
        return $this->hasOne(MasterAccountingCode::class, 'id', 'accounting_code_id');
    }

    /**
     * 売上伝票詳細 テーブルとのリレーション
     *
     * @return HasMany
     */
    public function salesOrderDetail(): HasMany
    {
        return $this->hasMany(SalesOrderDetail::class, 'product_id');
    }

    /**
     * 仕入伝票詳細 テーブルとのリレーション
     *
     * @return HasMany
     */
    public function purchaseOrderDetail(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'product_id');
    }

    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * 商品の単位名を取得
     *
     * @return string
     */
    public function getProductUnitNameAttribute(): string
    {
        return $this->mProductUnit->mUnit->name ?? '';
    }

    /**
     * カテゴリーコードを取得
     *
     * @return string
     */
    public function getCategoryCodeAttribute(): string
    {
        return $this->mCategory->code ?? '';
    }

    /**
     * カテゴリー名を取得
     *
     * @return string
     */
    public function getCategoryNameAttribute(): string
    {
        return $this->mCategory->name ?? '';
    }

    /**
     * サブカテゴリーコードを取得
     *
     * @return string
     */
    public function getSubCategoryCodeAttribute(): string
    {
        return $this->mSubCategory->code ?? '';
    }

    /**
     * サブカテゴリー名を取得
     *
     * @return string
     */
    public function getSubCategoryNameAttribute(): string
    {
        return $this->mSubCategory->name ?? '';
    }

    /**
     * 科目名を取得
     *
     * @return string
     */
    public function getAccountingCodeCodeAttribute(): string
    {
        return $this->mAccountingCode->code ?? '';
    }

    /**
     * 科目名を取得
     *
     * @return string
     */
    public function getAccountingCodeNameAttribute(): string
    {
        return $this->mAccountingCode->name ?? '';
    }

    /**
     * カテゴリー名＋サブカテゴリー名を取得
     *
     * @return string
     */
    public function getFullCategoryNameAttribute(): string
    {
        $fullCategoryName = '';
        if (!empty($this->category_name)) {
            $fullCategoryName = '>' . $this->category_name;
        }
        if (!empty($this->sub_category_name)) {
            $fullCategoryName .= '>' . $this->sub_category_name;
        }

        return $fullCategoryName;
    }

    /**
     * 種別コードを取得
     *
     * @return string
     */
    public function getKindCodeAttribute(): string
    {
        return $this->mKind->code ?? '';
    }

    /**
     * 種別名を取得
     *
     * @return string
     */
    public function getKindNameAttribute(): string
    {
        return $this->mKind->name ?? '';
    }

    /**
     * 管理部署コードを取得
     *
     * @return string
     */
    public function getSectionCodeAttribute(): string
    {
        return $this->mSection->code ?? '';
    }

    /**
     * 管理部署名を取得
     *
     * @return string
     */
    public function getSectionNameAttribute(): string
    {
        return $this->mSection->name ?? '';
    }

    /**
     * 分類１コードを取得
     *
     * @return string
     */
    public function getClassification1CodeAttribute(): string
    {
        return $this->mClassification1->code ?? '';
    }

    /**
     * 分類１名を取得
     *
     * @return string
     */
    public function getClassification1NameAttribute(): string
    {
        return $this->mClassification1->name ?? '';
    }

    /**
     * 分類２コードを取得
     *
     * @return string
     */
    public function getClassification2CodeAttribute(): string
    {
        return $this->mClassification2->code ?? '';
    }

    /**
     * 分類２名を取得
     *
     * @return string
     */
    public function getClassification2NameAttribute(): string
    {
        return $this->mClassification2->name ?? '';
    }

    /**
     * 仕入先コードを取得
     *
     * @return string
     */
    public function getSupplierCodeAttribute(): string
    {
        return $this->mSupplier->code ?? '';
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
     * 商品の単価を取得（単価小数桁数で切り捨て）
     *
     * @return string|null
     */
    public function getUnitPriceFloorAttribute(): ?string
    {
        if (is_null($this->unit_price)) {
            return null;
        }

        $unit_price_decimal_digit = $this->unit_price_decimal_digit ?? 0;
        $coef = pow(10, $unit_price_decimal_digit);
        $unit_price = floor($this->unit_price * $coef) / $coef;

        return sprintf("%.{$unit_price_decimal_digit}f", $unit_price);
    }

    /**
     * 商品の仕入単価を取得（単価小数桁数で切り捨て）
     *
     * @return string|null
     */
    public function getPurchaseUnitPriceFloorAttribute(): ?string
    {
        if (is_null($this->purchase_unit_price)) {
            return null;
        }

        $purchase_unit_price_decimal_digit = $this->unit_price_decimal_digit ?? 0;
        $coef = pow(10, $purchase_unit_price_decimal_digit);
        $purchase_unit_price = floor($this->purchase_unit_price * $coef) / $coef;

        return sprintf("%.{$purchase_unit_price_decimal_digit}f", $purchase_unit_price);
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
            ->paginate(config('consts.default.master.products.page_count'));
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
     * 検索結果を取得（在庫調整用）
     *
     * @param array $search_condition_input_data
     * @return Collection
     */
    public static function getProductInventoryStock(array $search_condition_input_data): Collection
    {
        $target_id = $search_condition_input_data['product_id'] ?? null;
        $target_category_id = $search_condition_input_data['category_id'] ?? null;
        $sort_type = $search_condition_input_data['sort'] ?? null;

        return self::query()
            ->when(isset($target_id), function ($query) use ($target_id) {
                return $query->where('id', $target_id);
            })
            ->when(isset($target_category_id), function ($query) use ($target_category_id) {
                return $query->where('category_id', $target_category_id);
            })
            ->when($sort_type == SortTypes::SYLLABARY_ORDER, function ($query) {
                return $query->oldest('name_kana');
            })
            ->when($sort_type == SortTypes::CATEGORY, function ($query) {
                return $query->oldest('category_id');
            })
            ->oldest('code')
            ->get();
    }

    /**
     * 商品データを取得
     * 単位データも合わせて
     *
     * @return Collection
     */
    public static function getProductData(): Collection
    {
        return self::query()
            ->with(['mProductUnit', 'mProductUnit.mUnit'])
            ->oldest('name_kana')   // コード値昇順
            ->get();
    }

    /**
     * POS連携用の商品データを取得
     *
     * @param string $target_date
     * @param string $limit_count
     * @return Collection
     */
    public static function getProductDataByPos(string $target_date, string $limit_count): Collection
    {
        return self::query()
            ->select([
                'm_products.code AS product_code',
                'm_products.name AS product_name',
                'm_products.name_kana AS product_name_kana',
                'jan_code',
                'unit_price',
                'tax_type_id',
                'm_units.name AS unit_name',
                DB::raw("CASE
                    WHEN m_products.deleted_at IS NULL OR CAST(m_products.deleted_at AS CHAR) = '' THEN 0
                    ELSE 1 END AS del_flg"),
                DB::raw("DATE_FORMAT(m_products.updated_at, '%Y/%m/%d') AS updated_date"),
                DB::raw("DATE_FORMAT(m_products.updated_at, '%H:%i:%s') AS updated_time"),
                'category_id',
                'm_categories.name AS category_name',
                'purchase_unit_price',
                'customer_product_code AS opponent_product_code',
                'specification',
                'kind_id',
                'm_kinds.code AS kind_code',
                'm_kinds.name AS kind_name',
                'section_id',
                'm_sections.code AS section_code',
                'm_sections.name AS section_name',
                'rack_address AS storage_number',
                'item_name AS second_product_name',
                'purchase_unit_weight',
                DB::raw('NULL AS tax_change_flg'),
                'reduced_tax_flag as reduced_tax_flg',
                'product_status',
            ])
            ->leftJoin('m_products_units', 'm_products.id', '=', 'm_products_units.product_id')
            ->leftJoin('m_units', 'm_products_units.unit_id', '=', 'm_units.id')
            ->leftJoin('m_categories', 'm_products.category_id', '=', 'm_categories.id')
            ->leftJoin('m_kinds', 'm_products.kind_id', '=', 'm_kinds.id')
            ->leftJoin('m_sections', 'm_products.section_id', '=', 'm_sections.id')
            ->where('m_products.updated_at', '>=', $target_date)
            ->orderBy('m_products.updated_at')
            ->orderBy('m_products.code')
            ->limit($limit_count)
            ->get();
    }

    /**
     * 単価小数桁数リスト取得
     *
     * @return array
     */
    public static function getUnitPriceDecimalDigitList(): array
    {
        $list = [];
        $min_value = MasterProductsConst::UNIT_PRICE_DECIMAL_DIGIT_MIN_VALUE;
        $max_value = MasterProductsConst::UNIT_PRICE_DECIMAL_DIGIT_MAX_VALUE;

        for ($i = $min_value; $i < $max_value + 1; ++$i) {
            $list[] = $i;
        }

        return $list;
    }

    /**
     * 数量小数桁数リスト取得
     *
     * @return array
     */
    public static function getQuantityDecimalDigitList(): array
    {
        $list = [];
        $min_value = MasterProductsConst::QUANTITY_DECIMAL_DIGIT_MIN_VALUE;
        $max_value = MasterProductsConst::QUANTITY_DECIMAL_DIGIT_MAX_VALUE;

        for ($i = $min_value; $i < $max_value + 1; ++$i) {
            $list[] = $i;
        }

        return $list;
    }

    /**
     * 商品（送料）の code を返却
     *
     * @return string[]
     */
    public static function getSendFeeGroupProductCode(): array
    {
        return ['1888887', '1888888', '1888889'];
    }

    // endregion static method

    /**
     * マスタの使用状況を返す
     *
     * @return bool
     */
    public function getUseMasterAttribute(): bool
    {
        return MasterIntegrityHelper::existsUseMasterProduct($this->id);
    }
}
