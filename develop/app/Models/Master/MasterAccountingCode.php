<?php

/**
 * 経理コードマスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use App\Consts\DB\Master\MasterAccountingCodesConst;
use App\Helpers\MasterIntegrityHelper;
use App\Helpers\StringHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 経理コードマスターモデル
 */
class MasterAccountingCode extends Model
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
    protected $table = 'm_accounting_codes';

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'note',
        'output_group',
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
        $this->code_length = MasterAccountingCodesConst::CODE_MAX_LENGTH;
    }

    // region eloquent-relationships

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

        return $query
            ->when(isset($target_id), function ($query) use ($target_id) {
                return $query->id($target_id['start'] ?? null, $target_id['end'] ?? null);
            })
            ->when(isset($target_code), function ($query) use ($target_code) {
                return $query->code($target_code['start'] ?? null, $target_code['end'] ?? null);
            })
            ->when(isset($target_name), function ($query) use ($target_name) {
                return $query->name($target_name);
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
        if (is_null($target_id_start) && is_null($target_id_end)) {
            return $query;
        }
        if (isset($target_id_start) && is_null($target_id_end)) {
            return $query->where('id', '>=', $target_id_start);
        }
        if (is_null($target_id_start) && isset($target_id_end)) {
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
     * 検索結果を取得（ページネーション）
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public static function getSearchResultPagenate(array $search_condition_input_data): LengthAwarePaginator
    {
        return self::query()
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.master.accounting_codes.page_count'));
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
        return MasterIntegrityHelper::existsUseMasterAccountingCode($this->id);
    }

    /**
     * 「売掛金」の id を返却
     *
     * @return mixed
     */
    public static function getAccountsReceivableId()
    {
        return self::query()
            ->where('name', '売掛金')
            ->value('id');
    }

    // region eloquent-accessors

    /**
     * 名称（ID付き）を取得
     *
     * @return string
     */
    public function getNameWithIdAttribute(): string
    {
        if ($this->name == null) {
            return '';
        }

        return StringHelper::getNameWithId($this->id, $this->name);
    }

    // endregion eloquent-accessors
}
