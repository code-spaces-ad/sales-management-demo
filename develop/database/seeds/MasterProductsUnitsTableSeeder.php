<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 商品_単位リレーションテーブル（m_products_units） Seeder Class
 */
class MasterProductsUnitsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET foreign_key_checks = 0');

        DB::table('m_products_units')->insert(
            [
                [
                    /** 商品ID */
                    'product_id' => 150101,
                    /** 単位ID */
                    'unit_id' => 1,
                ],
                [
                    /** 商品ID */
                    'product_id' => 150111,
                    /** 単位ID */
                    'unit_id' => 1,
                ],
                [
                    /** 商品ID */
                    'product_id' => 150209,
                    /** 単位ID */
                    'unit_id' => 1,
                ],
                [
                    /** 商品ID */
                    'product_id' => 150310,
                    /** 単位ID */
                    'unit_id' => 1,
                ],
                [
                    /** 商品ID */
                    'product_id' => 151436,
                    /** 単位ID */
                    'unit_id' => 1,
                ],
            ]
        );

        DB::statement('SET foreign_key_checks = 1');
    }
}
