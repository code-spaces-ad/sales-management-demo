<?php

namespace App\Models\Master;

use App\Consts\DB\Master\MasterCategoriesConst;
use App\Helpers\MasterIntegrityHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * カテゴリーマスターモデル
 */
class MasterCategory extends Model
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
    protected $table = 'm_categories';

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
        $this->code_length = MasterCategoriesConst::CODE_MAX_LENGTH;
    }

    // region eloquent-relationships

    /**
     * サブカテゴリマスター テーブルとのリレーション
     *
     * @return HasMany
     */
    public function mSubCategories(): HasMany
    {
        return $this->hasMany(MasterSubCategory::class, 'category_id');
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

        return $query->oldest('code')
            ->when($target_id !== null, function ($query) use ($target_id) {
                return $query->id($target_id['start'] ?? null, $target_id['end'] ?? null);
            })
            ->when($target_code !== null, function ($query) use ($target_code) {
                return $query->code($target_code['start'] ?? null, $target_code['end'] ?? null);
            })
            ->when($target_name !== null, function ($query) use ($target_name) {
                return $query->name($target_name);
            });
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
        return MasterIntegrityHelper::existsUseMasterCategory($this->id);
    }

    // endregion static method
}
