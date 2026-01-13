<?php

/**
 * 操作ログモデル
 */

namespace App\Models\System;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * 設定モデル
 */
class Settings extends Model
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
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'group',
        'value',
    ];

    /**
     * 非表示属性
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * JSONシリアル化時に追加するアクセサ
     *
     * @var array
     */
    protected $appends = [
        'created_at_slash',
        'updated_at_slash',
    ];

    /**
     * モデルの属性キャスト
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * 登録日（YYYY/MM/DD H:i:s)
     */
    public function getCreatedAtSlashAttribute(): string
    {
        return Carbon::parse($this->created_at)->format('Y/m/d H:i:s');
    }

    /**
     * 更新日（YYYY/MM/DD H:i:s)
     */
    public function getUpdatedAtSlashAttribute(): string
    {
        return Carbon::parse($this->updated_at)->format('Y/m/d H:i:s');
    }
}
