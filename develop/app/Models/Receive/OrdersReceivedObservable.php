<?php

/**
 * 発注伝票オブザーバ登録用 Trait
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Receive;

use App\Observers\OrdersReceivedObserver;

/**
 * 発注伝票オブザーバ登録用 Trait
 */
trait OrdersReceivedObservable
{
    /**
     * オブザーバ登録
     *
     * @return void
     */
    public static function bootOrdersReceivedObservable()
    {
        self::observe(OrdersReceivedObserver::class);
    }
}
