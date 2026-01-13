<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * カテゴリーマスターテーブル（m_categories） Seeder Class
 */
class MasterCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET foreign_key_checks = 0');

        DB::table('m_categories')->insert(
            [
                [
                    /** ID */
                    'id' => 1,
                    /** コード */
                    'code' => 1,
                    /** 名前 */
                    'name' => 'お茶',
                ],
                [
                    /** ID */
                    'id' => 9,
                    /** コード */
                    'code' => 9,
                    /** 名前 */
                    'name' => '内袋',
                ],
                [
                    /** ID */
                    'id' => 10,
                    /** コード */
                    'code' => 10,
                    /** 名前 */
                    'name' => 'その他',
                ],
                [
                    /** ID */
                    'id' => 11,
                    /** コード */
                    'code' => 11,
                    /** 名前 */
                    'name' => '茶道具',
                ],
                [
                    /** ID */
                    'id' => 13,
                    /** コード */
                    'code' => 13,
                    /** 名前 */
                    'name' => '箱',
                ],
                [
                    /** ID */
                    'id' => 14,
                    /** コード */
                    'code' => 14,
                    /** 名前 */
                    'name' => 'コーヒー',
                ],
                [
                    /** ID */
                    'id' => 15,
                    /** コード */
                    'code' => 15,
                    /** 名前 */
                    'name' => '仏事',
                ],
                [
                    /** ID */
                    'id' => 16,
                    /** コード */
                    'code' => 16,
                    /** 名前 */
                    'name' => 'バッグ',
                ],
            ]
        );

        DB::statement('SET foreign_key_checks = 1');
    }
}
