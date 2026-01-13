<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 端数処理方法マスターテーブル（m_rounding_methods） Seeder Class
 */
class MasterRoundingMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_rounding_methods')->insert(
            [
                [
                    'id'   => 1,
                    'name' => '切り捨て',
                ],
                [
                    'id'   => 2,
                    'name' => '切り上げ',
                ],
                [
                    'id'   => 3,
                    'name' => '四捨五入',
                ],
            ]
        );
    }
}
