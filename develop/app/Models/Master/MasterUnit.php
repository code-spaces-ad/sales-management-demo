<?php

/**
 * 単位マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use App\Helpers\StringHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 単位マスターモデル
 */
class MasterUnit extends Model
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
    protected $table = 'm_units';

    // region eloquent-accessors

    /**
     * 単位名（ID付き）を取得
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
