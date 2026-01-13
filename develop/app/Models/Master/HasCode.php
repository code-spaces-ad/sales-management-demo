<?php

/**
 * コード（code）用 Trait Class
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Builder;

/**
 * コード（code）用 Trait Class
 */
trait HasCode
{
    /**
     * @var int コード桁数
     */
    public $code_length = 4;

    /**
     * コード値カラム名取得
     *
     * @return string
     */
    public function getCodeColumn(): string
    {
        return defined('static::CODE') ? static::CODE : 'code';
    }

    // region eloquent-scope

    /**
     * 指定したコードのレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param int|null $target_code_start
     * @param int|null $target_code_end
     * @return Builder
     */
    public function scopeCode(Builder $query, ?int $target_code_start, ?int $target_code_end): Builder
    {
        if ($target_code_start === null && $target_code_end === null) {
            return $query;
        }

        if ($target_code_start !== null && $target_code_end === null) {
            return $query->where($this->getCodeColumn(), '>=', $target_code_start);
        }

        if ($target_code_start === null && $target_code_end !== null) {
            return $query->where($this->getCodeColumn(), '<=', $target_code_end);
        }

        return $query->whereBetween($this->getCodeColumn(), [$target_code_start, $target_code_end]);
    }

    // endregion eloquent-scope

    // region eloquent-accessors

    /**
     * コード値（ゼロ埋め）を取得
     *
     * @return string
     */
    public function getCodeZerofillAttribute(): string
    {
        return sprintf("%0{$this->code_length}d", $this->{$this->getCodeColumn()});
    }

    // endregion eloquent-accessors
}
