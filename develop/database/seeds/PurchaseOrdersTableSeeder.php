<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Models\Trading\PurchaseOrder;
use Illuminate\Database\Seeder;

/**
 * 発注伝票テーブル（purchase_orders） Seeder Class
 */
class PurchaseOrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(PurchaseOrder::class, 10)->create();
    }
}
