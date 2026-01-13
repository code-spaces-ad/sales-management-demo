<?php

/**
 * 権限マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use App\Helpers\StringHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 権限マスターモデル
 */
class MasterRole extends Model
{
    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'm_roles';

    // region eloquent-scope

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

    // endregion eloquent-scope

    // region eloquent-accessors

    /**
     * 権限名（ID付き）を取得
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

    // region static method

    /**
     * 入力項目のデータを取得する
     *
     * @param int $role_id
     * @param string|null $placeholder
     * @return array
     */
    public static function getInputItemByAuth(int $role_id, ?string $placeholder = null): array
    {
        $data = self::query()
            ->id($role_id, null)
            ->oldest('id')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name_with_id,
                ];
            })
            ->pluck('name', 'id');

        if ($placeholder !== null) {
            $data->prepend($placeholder, '');
        }

        return $data->all();
    }

    // endregion static method
}
