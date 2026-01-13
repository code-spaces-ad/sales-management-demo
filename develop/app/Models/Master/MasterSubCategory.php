<?php

namespace App\Models\Master;

use App\Consts\DB\Master\MasterSubCategoriesConst;
use App\Helpers\MasterIntegrityHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * サブカテゴリーマスターモデル
 */
class MasterSubCategory extends Model
{
    /**
     * コード用トレイト使用
     */
    use HasCode;

    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'm_sub_categories';

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'category_id',
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
        $this->code_length = MasterSubCategoriesConst::CODE_MAX_LENGTH;
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
        $target_category_code = $search_condition_input_data['category_code'] ?? null;
        $target_sub_category_code = $search_condition_input_data['sub_category_code'] ?? null;
        $target_category_id = $search_condition_input_data['category_id'] ?? null;
        $target_name = $search_condition_input_data['name'] ?? null;

        $select_recipient_column = [
            DB::raw('m_categories.name AS category_name'),
            DB::raw('m_categories.id AS category_id'),
            DB::raw('m_categories.code AS category_code'),
            DB::raw('m_sub_categories.id AS id'),
            DB::raw('m_sub_categories.name AS name'),
            DB::raw('m_sub_categories.code AS code'),
        ];

        return $query->select($select_recipient_column)
            ->leftJoin('m_categories', 'm_sub_categories.category_id', '=', 'm_categories.id')
            ->when($target_id !== null, function ($query) use ($target_id) {
                return $query->id($target_id['start'] ?? null, $target_id['end'] ?? null);
            })
            ->when($target_category_code !== null, function ($query) use ($target_category_code) {
                return $query->categoryCode($target_category_code['start'] ?? null, $target_category_code['end'] ?? null);
            })
            ->when($target_sub_category_code !== null, function ($query) use ($target_sub_category_code) {
                return $query->subCategoryCode($target_sub_category_code['start'] ?? null, $target_sub_category_code['end'] ?? null);
            })
            ->when(isset($target_category_id), function ($query) use ($target_category_id) {
                return $query->where('m_sub_categories.category_id', $target_category_id);
            })
            ->when($target_name !== null, function ($query) use ($target_name) {
                return $query->name($target_name);
            })
            ->oldest('m_categories.code')
            ->oldest('m_sub_categories.code');
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
     * 指定したカテゴリコードのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param string $target_name
     * @return Builder
     */
    public function scopeCategoryCode(Builder $query, ?int $target_code_start, ?int $target_code_end): Builder
    {
        if ($target_code_start === null && $target_code_end === null) {
            return $query;
        }

        if ($target_code_start !== null && $target_code_end === null) {
            return $query->where('m_categories.code', '>=', $target_code_start);
        }

        if ($target_code_start === null && $target_code_end !== null) {
            return $query->where('m_categories.code', '<=', $target_code_end);
        }

        return $query->whereBetween('m_categories.code', [$target_code_start, $target_code_end]);
    }

    /**
     * 指定したサブカテゴリコードのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param string $target_name
     * @return Builder
     */
    public function scopeSubCategoryCode(Builder $query, ?int $target_code_start, ?int $target_code_end): Builder
    {
        if ($target_code_start === null && $target_code_end === null) {
            return $query;
        }

        if ($target_code_start !== null && $target_code_end === null) {
            return $query->where('m_sub_categories.code', '>=', $target_code_start);
        }

        if ($target_code_start === null && $target_code_end !== null) {
            return $query->where('m_sub_categories.code', '<=', $target_code_end);
        }

        return $query->whereBetween('m_sub_categories.code', [$target_code_start, $target_code_end]);
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
        return $query->where('m_sub_categories.name', 'LIKE', '%' . $target_name . '%');
    }

    /**
     * カテゴリー リレーション情報を取得
     *
     * @return BelongsTo
     */
    public function mCategory(): BelongsTo
    {
        return $this->belongsTo(MasterCategory::class, 'category_id');
    }

    // endregion eloquent-scope

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
     * マスタの使用状況を返す
     *
     * @return bool
     */
    public function getUseMasterAttribute(): bool
    {
        return MasterIntegrityHelper::existsUseSubMasterCategory($this->id);
    }

    // endregion static method
}
