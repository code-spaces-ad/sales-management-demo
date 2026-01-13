<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * サブカテゴリーマスターテーブル（m_sub_categories） Seeder Class
 */
class MasterSubCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET foreign_key_checks = 0');

        DB::table('m_sub_categories')->insert(
            [
                [
                    /** ID */
                    'id' => 1,
                    /** コード */
                    'code' => 1,
                    /** カテゴリーID */
                    'category_id' => 1,
                    /** 名前 */
                    'name' => 'サブカテゴリー1',
                ],
                [
                    /** ID */
                    'id' => 91,
                    /** コード */
                    'code' => 91,
                    /** カテゴリーID */
                    'category_id' => 9,
                    /** 名前 */
                    'name' => 'サブカテゴリー2',
                ],
                [
                    /** ID */
                    'id' => 101,
                    /** コード */
                    'code' => 101,
                    /** カテゴリーID */
                    'category_id' => 10,
                    /** 名前 */
                    'name' => 'サブカテゴリー3',
                ],
                [
                    /** ID */
                    'id' => 102,
                    /** コード */
                    'code' => 102,
                    /** カテゴリーID */
                    'category_id' => 10,
                    /** 名前 */
                    'name' => 'サブカテゴリー4',
                ],
                [
                    /** ID */
                    'id' => 103,
                    /** コード */
                    'code' => 103,
                    /** カテゴリーID */
                    'category_id' => 10,
                    /** 名前 */
                    'name' => 'サブカテゴリー5',
                ],
                [
                    /** ID */
                    'id' => 151,
                    /** コード */
                    'code' => 151,
                    /** カテゴリーID */
                    'category_id' => 10,
                    /** 名前 */
                    'name' => 'サブカテゴリー6',
                ],
                [
                    /** ID */
                    'id' => 152,
                    /** コード */
                    'code' => 152,
                    /** カテゴリーID */
                    'category_id' => 10,
                    /** 名前 */
                    'name' => 'その他',
                ],
                [
                    /** ID */
                    'id' => 153,
                    /** コード */
                    'code' => 153,
                    /** カテゴリーID */
                    'category_id' => 10,
                    /** 名前 */
                    'name' => '送料',
                ],
            ]
        );

        DB::statement('SET foreign_key_checks = 1');
    }
}
