<?php

/**
 * 操作ログモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\System;

use App\Enums\UserRoleType;
use App\Models\Master\MasterUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 操作ログモデル
 */
class LogOperation extends Model
{
    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'log_operations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'route_name',
        'request_url',
        'request_method',
        'status_code',
        'request_message',
        'remote_addr',
        'user_agent',
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
        $target_created_at = $search_condition_input_data['created_at'] ?? null;

        return $query->orderByDesc('log_operations.created_at')     // created_at 降順
            ->when(auth()->user()->role_id != UserRoleType::SYS_ADMIN, function ($query) {
                return $query->whereHas('mUser', function ($query) {
                    $query->where('role_id', '<>', UserRoleType::SYS_ADMIN);
                });
            })
            ->when(isset($target_created_at['start']), function ($query) use ($target_created_at) {
                $target_created_at_start = new Carbon($target_created_at['start']);

                return $query->where('log_operations.created_at', '>=', $target_created_at_start);
            })
            ->when(isset($target_created_at['end']), function ($query) use ($target_created_at) {
                $target_created_at_end = new Carbon($target_created_at['end'] . ' +1 day');

                return $query->where('log_operations.created_at', '<', $target_created_at_end);
            });
    }

    // endregion eloquent-scope

    // region eloquent-relationships

    /**
     * m_users テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mUser(): BelongsTo
    {
        return $this->belongsTo(MasterUser::class, 'user_id');
    }

    // endregion eloquent-relationships

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
            ->with(['mUser'])
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.system.log_operations.page_count'));
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
     * 対象期間外ログの古いIDを返す
     *
     * @param Carbon $target_date
     * @return null|int
     */
    public static function getOutOfTermId(Carbon $target_date): ?int
    {
        return self::query()
            ->whereDate('created_at', '<=', $target_date)
            ->oldest('created_at')
            ->value('id');
    }

    // endregion static method
}
