<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Models\PurchaseInvoice\PurchaseClosing;
use Carbon\Carbon;

/**
 * 支払締処理ヘルパークラス
 */
class PurchaseClosingHelper
{
    /**
     * 指定の支払先・年月(未来含む)の請求締処理状況を返す
     *
     * @param int $supplier_id
     * @param Carbon $purchase_date
     * @return bool
     */
    public static function getPurchaseClosing(int $supplier_id, Carbon $purchase_date): bool
    {
        return PurchaseClosing::where('supplier_id', $supplier_id)
            ->where('purchase_closing_start_date', '<=', $purchase_date)
            ->where('purchase_closing_end_date', '>=', $purchase_date)
            ->exists();
    }
}
