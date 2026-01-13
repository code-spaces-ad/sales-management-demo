<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 社員マスターテーブル（m_employees） Seeder Class
 */
class MasterEmployeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_employees')->insert(
            [
                [
                    /** ID */
                    'id' => 1,
                    /** コード */
                    'code' => 1,
                    /** 名前 */
                    'name' => 'テスト社員1',
                    /** 名前かな */
                    'name_kana' => 'てすとしゃいんいち',
                    'department_id' => 100,
                    'office_facilities_id' => 110,
                ],
                [
                    /** ID */
                    'id' => 2,
                    /** コード */
                    'code' => 2,
                    /** 名前 */
                    'name' => 'テスト社員2',
                    /** 名前かな */
                    'name_kana' => 'てすとしゃいんに',
                    'department_id' => 100,
                    'office_facilities_id' => 110,
                ],
                [
                    /** ID */
                    'id' => 3,
                    /** コード */
                    'code' => 3,
                    /** 名前 */
                    'name' => 'テスト社員3',
                    /** 名前かな */
                    'name_kana' => 'てすとしゃいんさん',
                    'department_id' => 100,
                    'office_facilities_id' => 2140,
                ],
                [
                    /** ID */
                    'id' => 4,
                    /** コード */
                    'code' => 4,
                    /** 名前 */
                    'name' => 'テスト社員4',
                    /** 名前かな */
                    'name_kana' => 'てすとしゃいんよん',
                    'department_id' => 1000,
                    'office_facilities_id' => 2140,
                ],
                [
                    /** ID */
                    'id' => 5,
                    /** コード */
                    'code' => 5,
                    /** 名前 */
                    'name' => 'テスト社員5',
                    /** 名前かな */
                    'name_kana' => 'てすとしゃいんご',
                    'department_id' => 2000,
                    'office_facilities_id' => 2150,
                ],
                [
                    /** ID */
                    'id' => 99999,
                    /** コード */
                    'code' => 99999,
                    /** 名前 */
                    'name' => 'システム管理者',
                    /** 名前かな */
                    'name_kana' => 'しすてむかんりしゃ',
                    'department_id' => null,
                    'office_facilities_id' => null,
                ],
            ]
        );
    }
}
