<?php

/**
 * 納品先マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use App\Helpers\MasterIntegrityHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 納品先マスターモデル
 */
class MasterRecipient extends Model
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
    protected $table = 'm_recipients';

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'name_kana',
        'branch_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // region eloquent-scope

    /**
     * selectbox用の一覧取得（取引先込み）
     *
     * @return mixed
     */
    public static function getListSelectBox()
    {
        $select_recipient_column = [
            DB::raw('m_recipients.*'),
            DB::raw('m_customers.id AS customer_id'),
        ];

        return self::select($select_recipient_column)
            ->Join('m_branches', 'm_recipients.branch_id', '=', 'm_branches.id')
            ->Join('m_customers', 'm_branches.customer_id', '=', 'm_customers.id')
            ->oldest('m_recipients.id')  // ID昇順
            ->get();
    }

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
        $target_branch_id = $search_condition_input_data['branch_id'] ?? null;
        $target_recipient_name = $search_condition_input_data['recipient_name'] ?? null;

        $select_recipient_column = [
            DB::raw('m_customers.name AS customer_name'),
            DB::raw('m_branches.id AS branch_id'),
            DB::raw('m_branches.name AS branch_name'),
            DB::raw('m_recipients.id AS recipient_id'),
            DB::raw('m_recipients.name AS recipient_name'),
            DB::raw('m_recipients.name_kana AS recipient_name_kana'),
        ];

        return $query->select($select_recipient_column)
            ->Join('m_branches', 'm_recipients.branch_id', '=', 'm_branches.id')
            ->Join('m_customers', 'm_branches.customer_id', '=', 'm_customers.id')
            ->when($target_customer_id !== null, function ($query) use ($target_customer_id) {
                return $query->customerId($target_customer_id);
            })
            ->when($target_branch_id !== null, function ($query) use ($target_branch_id) {
                return $query->branchId($target_branch_id);
            })
            ->when($target_recipient_name !== null, function ($query) use ($target_recipient_name) {
                return $query->recipientName($target_recipient_name);
            })
            ->oldest('m_customers.code') // 得意先コード
            ->oldest('m_branches.id')    // 支所ID
            ->oldest('m_recipients.id'); // 納品先ID
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

    /**
     * 指定した支所名のレコードだけに限定するクエリースコープ(部分一致)
     *
     * @param Builder $query
     * @param string $target_branch_name
     * @return Builder
     */
    public function scopeBranchName(Builder $query, string $target_branch_name): Builder
    {
        return $query->where('m_branches.name', 'LIKE', '%' . $target_branch_name . '%');
    }

    public function scopeCustomerId(Builder $query, string $target_customer_id): Builder
    {
        return $query->where('m_customers.id', '=', $target_customer_id);
    }

    public function scopeBranchId(Builder $query, string $target_branch_id): Builder
    {
        return $query->where('m_branches.id', '=', $target_branch_id);
    }

    /**
     * 指定した納品先名のレコードだけに限定するクエリースコープ(部分一致)
     *
     * @param Builder $query
     * @param string $target_recipient_name
     * @return Builder
     */
    public function scopeRecipientName(Builder $query, string $target_recipient_name): Builder
    {
        return $query->where('m_recipients.name', 'LIKE', '%' . $target_recipient_name . '%');
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
            ->paginate(config('consts.default.master.recipients.page_count'));
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
        return MasterIntegrityHelper::existsUseMasterRecipient($this->id);
    }

    /**
     * 条件を元にデータ取得
     *
     * @param string $branchId
     * @param string $recipientName
     * @return Builder|Model|object|null
     */
    public static function getRecipientByBrachAndRecipientName(string $branchId, string $recipientName)
    {
        return self::query()
            ->where('branch_id', '=', $branchId)
            ->where('name', '=', $recipientName)
            ->first();
    }

    /**
     * m_branches テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mBranch(): BelongsTo
    {
        return $this->belongsTo(MasterBranch::class, 'branch_id');
    }

    /**
     * 得意先コード（ゼロ埋め）を取得
     *
     * @return string
     */
    public function getCustomerCodeZerofillAttribute(): string
    {
        return $this->mBranch->mCustomer->code_zerofill ?? '';
    }
}
