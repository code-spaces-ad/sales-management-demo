<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Models\Invoice\ChargeData;
use Carbon\Carbon;

/**
 * 請求締処理ヘルパークラス
 */
class ChargeClosingHelper
{
    /**
     * 指定の請求先・年月(未来含む)の請求締処理状況を返す
     *
     * @param int $customer_id
     * @param Carbon $charge_date
     * @return bool
     */
    public static function getChargeClosing(int $customer_id, Carbon $charge_date): bool
    {
        return ChargeData::where('customer_id', $customer_id)
            // ->where('closing_ym', '>=', $closing_ym)
            ->where('charge_start_date', '<=', $charge_date)
            ->where('charge_end_date', '>=', $charge_date)
            ->exists();
    }
}
