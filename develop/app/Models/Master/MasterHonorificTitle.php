<?php

/**
 * 敬称マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use App\Helpers\StringHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 敬称マスターモデル
 */
class MasterHonorificTitle extends Model
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
    protected $table = 'm_honorific_titles';

    // region eloquent-accessors

    /**
     * 敬称名（ID付き）を取得
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

    /**
     * 敬称名(様)を取得
     *
     * @return string
     */
    public function getNameFixedAttribute(): string
    {
        // 敬称"様"固定
        return self::query()
            ->where('id', 2)
            ->value('name');
    }

    // endregion eloquent-accessors

    // region static method

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

    // endregion static method
}
