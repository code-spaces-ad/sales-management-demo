<?php

/**
 * 在庫データオブザーバ登録用 Trait
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Inventory;

use App\Observers\InventoryDataObserver;

/**
 * 在庫データオブザーバ登録用 Trait
 */
trait InventoryDataObservable
{
    /**
     * オブザーバ登録
     *
     * @return void
     */
    public static function bootInventoryDataObservable()
    {
        self::observe(InventoryDataObserver::class);
    }
}
