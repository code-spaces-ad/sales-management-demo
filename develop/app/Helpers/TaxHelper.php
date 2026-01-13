<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Enums\RoundingMethodType;
use App\Models\Master\MasterConsumptionTax;

/**
 * 税率用ヘルパークラス
 */
class TaxHelper
{
    /**
     * 税込金額(税率端数処理)を取得
     *
     * @param float $price
     * @param int $tax_rate
     * @param int $rounding_method_id
     * @return float
     */
    public static function getIncTax(float $price, int $tax_rate, int $rounding_method_id): float
    {
        // 税率端数処理
        if ($tax_rate > 0) {
            // 切捨て
            if ($rounding_method_id === RoundingMethodType::ROUND_DOWN) {
                return floor($price * (1 + ($tax_rate / 100)));
            }
            // 切上げ
            if ($rounding_method_id === RoundingMethodType::ROUND_UP) {
                return ceil($price * (1 + ($tax_rate / 100)));
            }
            // 四捨五入
            if ($rounding_method_id === RoundingMethodType::ROUND_OFF) {
                return round($price * (1 + ($tax_rate / 100)));
            }
        }

        return $price;
    }

    /**
     * 税金額(税率端数処理)を取得
     *
     * @param float $price
     * @param int $tax_rate
     * @param int $rounding_method_id
     * @return float
     */
    public static function getTax(float $price, int $tax_rate, int $rounding_method_id): float
    {
        // 税率端数処理
        if ($tax_rate > 0) {
            // 切捨て
            if ($rounding_method_id === RoundingMethodType::ROUND_DOWN) {
                return floor($price * ($tax_rate / 100));
            }
            // 切上げ
            if ($rounding_method_id === RoundingMethodType::ROUND_UP) {
                return ceil($price * ($tax_rate / 100));
            }
            // 四捨五入
            if ($rounding_method_id === RoundingMethodType::ROUND_OFF) {
                return round($price * ($tax_rate / 100));
            }
        }

        return $price;
    }

    /**
     * 内税金額(切捨て端数処理)を取得
     *
     * @param float $price
     * @param int $tax_rate
     * @param int $rounding_method_id
     * @return float
     */
    public static function getInTax(float $price, int $tax_rate, int $rounding_method_id): float
    {
        if ($tax_rate > 0) {
            // 切捨て
            if ($rounding_method_id === RoundingMethodType::ROUND_DOWN) {
                return $price - floor($price / (1 + ($tax_rate / 100)));
            }
            // 切上げ
            if ($rounding_method_id === RoundingMethodType::ROUND_UP) {
                return $price - ceil($price / (1 + ($tax_rate / 100)));
            }
            // 四捨五入
            if ($rounding_method_id === RoundingMethodType::ROUND_OFF) {
                return $price - round($price / (1 + ($tax_rate / 100)));
            }
        }

        return 0;
    }

    /**
     * 指定日の税率を取得
     *
     * @param string $target_date
     * @return array
     */
    public static function getTaxRate(string $target_date): array
    {
        $tax_data = MasterConsumptionTax::query()
            ->where('begin_date', '<=', $target_date)
            ->orderByDesc('begin_date')
            ->first();

        return [
            'normal_tax_rate' => $tax_data->normal_tax_rate,
            'reduced_tax_rate' => $tax_data->reduced_tax_rate,
        ];
    }
}
