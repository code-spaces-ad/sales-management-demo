<?php

/**
 * 名前かな（name_kana）用 Trait Class
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Builder;

/**
 * 名前かな（name_kana）用 Trait Class
 */
trait HasNameKana
{
    /**
     * 名前かなカラム名取得
     *
     * @return string
     */
    public function getNameKanaColumn(): string
    {
        return defined('static::NAME_KANA') ? static::NAME_KANA : 'name_kana';
    }

    // region eloquent-scope

    /**
     * 指定した名前かなのレコードだけに限定するクエリースコープ(部分一致)
     *
     * @param Builder $query
     * @param string $target_name_kana
     * @return Builder
     */
    public function scopeNameKana(Builder $query, string $target_name_kana): Builder
    {
        return $query->where($this->getNameKanaColumn(), 'LIKE', '%' . $target_name_kana . '%');
    }

    // endregion eloquent-scope
}
