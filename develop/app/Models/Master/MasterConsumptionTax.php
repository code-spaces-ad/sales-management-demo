<?php

/**
 * 消費税マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 消費税マスターモデル
 */
class MasterConsumptionTax extends Model
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
    protected $table = 'm_consumption_taxes';

    // region static method

    /**
     * 選択用リストを取得
     *
     * @return array
     */
    public static function getList(): array
    {
        // 現在の時刻を取得
        $now_date = Carbon::now()->format('Y-m-d');

        $tax_rate = optional(self::query()
            ->where('begin_date', '<=', $now_date)
            ->orderByDesc('id')
            ->first(['normal_tax_rate', 'reduced_tax_rate']))
            ->toArray() ?? [];

        $result = [];
        if (array_key_exists('normal_tax_rate', $tax_rate) && !is_null($tax_rate['normal_tax_rate'])) {
            $result[$tax_rate['normal_tax_rate']] = $tax_rate['normal_tax_rate'] . '％';
        }

        if (array_key_exists('reduced_tax_rate', $tax_rate) && !is_null($tax_rate['reduced_tax_rate'])) {
            $result[$tax_rate['reduced_tax_rate']] = $tax_rate['reduced_tax_rate'] . '％';
        }

        $result[0] = '非課税';

        return $result;
    }

    /**
     * 税率取得用リストを取得
     *
     * @return array
     */
    public static function getTaxValueList(): array
    {
        // 現在の時刻を取得
        $now_date = Carbon::now()->format('Y-m-d');

        $tax_rate = optional(self::query()
            ->where('begin_date', '<=', $now_date)
            ->orderByDesc('id')
            ->first(['normal_tax_rate', 'reduced_tax_rate']))
            ->toArray() ?? [];

        $result = [];
        if (array_key_exists('normal_tax_rate', $tax_rate) && !is_null($tax_rate['normal_tax_rate'])) {
            $result['normal_tax_rate'] = $tax_rate['normal_tax_rate'];
        }

        if (array_key_exists('reduced_tax_rate', $tax_rate) && !is_null($tax_rate['reduced_tax_rate'])) {
            $result['reduced_tax_rate'] = $tax_rate['reduced_tax_rate'];
        }

        return $result;
    }

    // endregion static method
}
