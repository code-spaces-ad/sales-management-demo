<?php

/**
 * 帳簿在庫数用モデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Sale\Ledger;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerStocks extends Model
{
    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'closing_ym',
        'ledger_stocks',
    ];

    /**
     * 帳簿在庫数を取得
     *
     * @param string|null $product_id
     * @param string $closing_ym
     * @return Builder|Model|object|null
     */
    public static function getLastLedgerStocks(?string $product_id, string $closing_ym): ?self
    {
        // 前月の帳簿在庫数
        return self::query()
            ->when($product_id, function ($query) use ($product_id) {
                // 商品IDで絞り込み
                return $query->where('product_id', $product_id);
            })
            ->where('closing_ym', '<', $closing_ym)
            ->latest('closing_ym')
            ->first();
    }
}
