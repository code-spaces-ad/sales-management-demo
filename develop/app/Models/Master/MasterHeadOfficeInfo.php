<?php

/**
 * 本社情報マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 本社情報マスターモデル
 * ※１レコードのみ
 */
class MasterHeadOfficeInfo extends Model
{
    /**
     * 固定レコードID
     */
    public const FIXED_ID = 1;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'm_head_office_information';

    /**
     * プライマリーキー無効
     */
    protected $primaryKey = null;

    /**
     * AutoIncrement無効
     */
    public $incrementing = false;

    /**
     * @return string
     */
    public static function getCompanyName(): string
    {
        return self::query()->first()->company_name ?? '';
    }

    /**
     * 固定レコードだけに限定するクエリースコープ
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFixedOnly(Builder $query): Builder
    {
        return $query->where('id', self::FIXED_ID);
    }

    /**
     * 期首月を取得
     *
     * @return string
     */
    public static function getFiscalYear(): string
    {
        return self::query()->value('fiscal_year');
    }
}
