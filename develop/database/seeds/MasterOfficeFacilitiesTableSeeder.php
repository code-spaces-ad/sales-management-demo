<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 　事業所マスターテーブル（m_office_facilities） Seeder Class
 */
class MasterOfficeFacilitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_office_facilities')->insert(
            [
                [
                    'id' => 1,
                    'code' => 100,
                    'department_id' => 1,
                    'name' => '本社',
                    'manager_id' => 1,
                ],
                [
                    'id' => 2,
                    'code' => 2140,
                    'department_id' => 3,
                    'name' => '国内営業',
                    'manager_id' => null,
                ],
                [
                    'id' => 3,
                    'code' => 2150,
                    'department_id' => 3,
                    'name' => '海外営業',
                    'manager_id' => null,
                ],
            ]
        );
    }
}
