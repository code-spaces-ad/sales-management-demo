<?php

/**
 * 発注伝票オブザーバ登録用 Trait
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Trading;

use App\Observers\PurchaseOrderObserver;

/**
 * 発注伝票オブザーバ登録用 Trait
 */
trait PurchaseOrderObservable
{
    /**
     * オブザーバ登録
     *
     * @return void
     */
    public static function bootPurchaseOrderObservable()
    {
        self::observe(PurchaseOrderObserver::class);
    }
}
