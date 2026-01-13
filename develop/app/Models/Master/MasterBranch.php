<?php

/**
 * 支所マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use App\Helpers\MasterIntegrityHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 支所マスターモデル
 */
class MasterBranch extends Model
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
    protected $table = 'm_branches';

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'name_kana',
        'mnemonic_name',
        'customer_id',
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
        $target_customer_id = $search_condition_input_data['customer_id'] ?? null;
        $target_branch_name = $search_condition_input_data['branch_name'] ?? null;

        $select_branch_column = [
            DB::raw('m_branches.id AS branch_id'),
            DB::raw('m_branches.name AS branch_name'),
            DB::raw('m_branches.name_kana AS name_kana'),
            DB::raw('m_branches.mnemonic_name AS mnemonic_name'),
            DB::raw('m_customers.id AS customer_id'),
            DB::raw('m_customers.code AS customer_code'),
            DB::raw('m_customers.name AS customer_name'),
        ];

        return $query->select($select_branch_column)
            ->Join('m_customers', 'm_branches.customer_id', '=', 'm_customers.id')
            ->oldest('customer_code')  // code昇順
            ->oldest('m_customers.id')  // code昇順
            ->when($target_customer_id !== null, function ($query) use ($target_customer_id) {
                return $query->customerId($target_customer_id);
            })
            ->when($target_branch_name !== null, function ($query) use ($target_branch_name) {
                return $query->branchName($target_branch_name);
            });
    }

    /**
     * 指定した得意先コードのレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param int|null $target_code_start
     * @param int|null $target_code_end
     * @return Builder
     */
    public function scopeCustomerCode(Builder $query, ?int $target_code_start, ?int $target_code_end): Builder
    {
        $customer_code = 'm_customers.code';

        if ($target_code_start === null && $target_code_end === null) {
            return $query;
        }
        if ($target_code_start !== null && $target_code_end === null) {
            return $query->where($customer_code, '>=', $target_code_start);
        }
        if ($target_code_start === null && $target_code_end !== null) {
            return $query->where($customer_code, '<=', $target_code_end);
        }

        return $query->whereBetween($customer_code, [$target_code_start, $target_code_end]);
    }

    /**
     * 指定した得意先名のレコードだけに限定するクエリースコープ(部分一致)
     *
     * @param Builder $query
     * @param string $target_customer_name
     * @return Builder
     */
    public function scopeCustomerName(Builder $query, string $target_customer_name): Builder
    {
        return $query->where('m_customers.name', 'LIKE', '%' . $target_customer_name . '%');
    }

    public function scopeCustomerId(Builder $query, string $target_customer_id): Builder
    {
        return $query->where('m_customers.id', '=', $target_customer_id);
    }

    public function scopeBranchName(Builder $query, string $target_branch_name): Builder
    {
        return $query->where('m_branches.name', 'LIKE', '%' . $target_branch_name . '%');
    }

    /**
     * 指定した得意先名かなのレコードだけに限定するクエリースコープ(部分一致)
     *
     * @param Builder $query
     * @param string $target_customer_name_kana
     * @return Builder
     */
    public function scopeCustomerNameKana(Builder $query, string $target_customer_name_kana): Builder
    {
        return $query
            ->where('m_customers.name_kana', 'LIKE', '%' . $target_customer_name_kana . '%');
    }

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
            ->paginate(config('consts.default.master.branches.page_count'));
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
        return MasterIntegrityHelper::existsUseMasterBranch($this->id);
    }

    /**
     * 得意先 リレーション情報を取得
     *
     * @return BelongsTo
     */
    public function mCustomer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    /**
     * 得意先コード（ゼロ埋め）を取得
     *
     * @return string
     */
    public function getCustomerCodeZerofillAttribute(): string
    {
        return $this->mCustomer->code_zerofill ?? '';
    }

    /**
     * 得意先を取得
     *
     * @return string
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->mCustomer->name ?? '';
    }

    /**
     * 納品先 リレーション情報を取得
     *
     * @return HasMany
     */
    public function mRecipients(): HasMany
    {
        return $this->hasMany(MasterRecipient::class, 'branch_id');
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
}
