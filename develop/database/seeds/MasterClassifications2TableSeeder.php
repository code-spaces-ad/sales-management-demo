<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 分類2マスターテーブル（m_classifications2） Seeder Class
 */
class MasterClassifications2TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET foreign_key_checks = 0');

        DB::table('m_classifications2')->insert(
            [
                [
                    /** ID */
                    'id' => 1,
                    /** コード */
                    'code' => 1,
                    /** 名前 */
                    'name' => '煎茶',
                ],
                [
                    /** ID */
                    'id' => 2,
                    /** コード */
                    'code' => 2,
                    /** 名前 */
                    'name' => '単一品種煎茶',
                ],
                [
                    /** ID */
                    'id' => 3,
                    /** コード */
                    'code' => 3,
                    /** 名前 */
                    'name' => '蒸しぐり茶',
                ],
                [
                    /** ID */
                    'id' => 4,
                    /** コード */
                    'code' => 4,
                    /** 名前 */
                    'name' => '番茶',
                ],
                [
                    /** ID */
                    'id' => 5,
                    /** コード */
                    'code' => 5,
                    /** 名前 */
                    'name' => '茎茶',
                ],
                [
                    /** ID */
                    'id' => 6,
                    /** コード */
                    'code' => 6,
                    /** 名前 */
                    'name' => '切断茶・粉茶',
                ],
                [
                    /** ID */
                    'id' => 7,
                    /** コード */
                    'code' => 7,
                    /** 名前 */
                    'name' => 'ほうじ茶',
                ],
            ]
        );

        DB::statement('SET foreign_key_checks = 1');
    }
}
