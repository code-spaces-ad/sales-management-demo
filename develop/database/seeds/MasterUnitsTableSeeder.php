<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 単位マスターテーブル（m_units） Seeder Class
 */
class MasterUnitsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_units')->insert(
            [
                [
                    /** 単位ID */
                    'id' => 1,
                    /** 単位コード */
                    'code' => 1,
                    /** 単位名 */
                    'name' => 'pcs',
                ],
            ]
        );
    }
}
