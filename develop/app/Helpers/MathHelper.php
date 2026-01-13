<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Enums\RoundingMethodType;

/**
 * 計算ヘルパークラス
 */
class MathHelper
{
    /**
     * 端数処理算出値を取得
     *
     * @param float $value
     * @param int $decimal_digit
     * @param int $rounding_method_id
     * @return float
     */
    public static function getRoundingValue(float $value, int $decimal_digit, int $rounding_method_id): float
    {
        // 重み（計算用）
        $weight = pow(10, $decimal_digit);
        // 切捨て
        if ($rounding_method_id === RoundingMethodType::ROUND_DOWN) {
            return floor($value * $weight) / $weight;
        }
        // 切上げ
        if ($rounding_method_id === RoundingMethodType::ROUND_UP) {
            return ceil($value * $weight) / $weight;
        }
        // 四捨五入
        if ($rounding_method_id === RoundingMethodType::ROUND_OFF) {
            return round($value, $decimal_digit);
        }

        return $value;
    }
}
