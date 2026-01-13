<?php

/**
 * ユーザーマスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use App\Consts\DB\Master\MasterUsersConst;
use App\Helpers\UserHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * ユーザーマスターモデル
 */
class MasterUser extends Authenticatable
{
    /**
     * コード用トレイト使用
     */
    use HasCode;

    /**
     * Laravelの通知機能
     */
    use Notifiable;

    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'm_users';

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
        $this->code_length = MasterUsersConst::CODE_MAX_LENGTH;
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
        $target_login_id = $search_condition_input_data['login_id'] ?? null;
        $target_name = $search_condition_input_data['name'] ?? null;
        $target_role_ids = $search_condition_input_data['role_id'] ?? null;
        $target_auth_role_id = $search_condition_input_data['auth_role_id'] ?? null;

        return $query
            ->when($target_id !== null, function ($query) use ($target_id) {
                return $query->id($target_id['start'] ?? null, $target_id['end'] ?? null);
            })
            ->when($target_code !== null, function ($query) use ($target_code) {
                return $query->code($target_code['start'] ?? null, $target_code['end'] ?? null);
            })
            ->when($target_login_id !== null, function ($query) use ($target_login_id) {
                return $query->loginId($target_login_id);
            })
            ->when($target_name !== null, function ($query) use ($target_name) {
                return $query->name($target_name);
            })
            ->when($target_role_ids !== null, function ($query) use ($target_role_ids) {
                return $query->roleId($target_role_ids);
            })
            ->when($target_auth_role_id !== null, function ($query) use ($target_auth_role_id) {
                return $query->authRoleId($target_auth_role_id);
            })
            // コード昇順
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
     * 指定したログインIDのレコードだけに限定するクエリースコープ(部分一致)
     *
     * @param Builder $query
     * @param string $target_login_id
     * @return Builder
     */
    public function scopeLoginId(Builder $query, string $target_login_id): Builder
    {
        return $query->where('login_id', 'LIKE', '%' . $target_login_id . '%');
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
     * 指定した権限IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $target_role_ids
     * @return Builder
     */
    public function scopeRoleId(Builder $query, array $target_role_ids): Builder
    {
        return $query->whereIn('role_id', $target_role_ids);
    }

    /**
     * 指定した権限IDのレコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param string $target_auth_role_id
     * @return Builder
     */
    public function scopeAuthRoleId(Builder $query, string $target_auth_role_id): Builder
    {
        if (UserHelper::isRoleEmployee($target_auth_role_id)) {
            return $query->where('id', '=', Auth::user()->id);
        }

        return $query->where('role_id', '>=', $target_auth_role_id);
    }

    // endregion eloquent-scope

    // region eloquent-relationships

    /**
     * 権限テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mRole(): BelongsTo
    {
        return $this->belongsTo(MasterRole::class, 'role_id');
    }

    public function mEmployee(): BelongsTo
    {
        return $this->belongsTo(MasterEmployee::class, 'employee_id');
    }
    // endregion eloquent-relationships

    // region eloquent-accessors

    /**
     * ID（ゼロ埋め）を取得
     *
     * @return null|string
     */
    public function getIdZerofillAttribute(): ?string
    {
        if (!isset($this->id)) {
            return null;
        }

        $id_length = MasterUsersConst::ID_MAX_LENGTH;

        return sprintf("%0{$id_length}d", $this->id);
    }

    /**
     * 権限名を取得
     *
     * @return string
     */
    public function getRoleNameAttribute(): string
    {
        return $this->mRole->name ?? '';
    }

    public function getEmplyoeeNameAttribute(): string
    {
        return $this->mEmployee->name ?? '';
    }

    /**
     * ログインユーザーかどうか
     *
     * @return bool
     */
    public function getIsLoginUserAttribute(): bool
    {
        return $this->id === auth()->user()->id;
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
            ->paginate(config('consts.default.master.users.page_count'));
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
     * "unknown"ユーザーを取得
     *
     * @return int
     */
    public static function getUnknownUser(): int
    {
        return self::query()
            ->latest('id')
            ->value('id');
    }

    // endregion static method
}
