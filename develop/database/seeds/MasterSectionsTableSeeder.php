<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 種別マスターテーブル（m_sections） Seeder Class
 */
class MasterSectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET foreign_key_checks = 0');

        DB::table('m_sections')->insert(
            [
                [
                    /** ID */
                    'id' => 1,
                    /** コード */
                    'code' => 1,
                    /** 名前 */
                    'name' => '卸課',
                ],
                [
                    /** ID */
                    'id' => 2,
                    /** コード */
                    'code' => 2,
                    /** 名前 */
                    'name' => '小売支援課',
                ],
                [
                    /** ID */
                    'id' => 3,
                    /** コード */
                    'code' => 3,
                    /** 名前 */
                    'name' => '製造1課',
                ],
                [
                    /** ID */
                    'id' => 4,
                    /** コード */
                    'code' => 4,
                    /** 名前 */
                    'name' => '総務部',
                ],
                [
                    /** ID */
                    'id' => 5,
                    /** コード */
                    'code' => 5,
                    /** 名前 */
                    'name' => '販売用',
                ],
                [
                    /** ID */
                    'id' => 6,
                    /** コード */
                    'code' => 6,
                    /** 名前 */
                    'name' => '営業推進部',
                ],
            ]
        );

        DB::statement('SET foreign_key_checks = 1');
    }
}
