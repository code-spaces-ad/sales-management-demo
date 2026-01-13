<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 分類1マスターテーブル（m_classifications1） Seeder Class
 */
class MasterClassifications1TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET foreign_key_checks = 0');

        DB::table('m_classifications1')->insert(
            [
                [
                    /** ID */
                    'id' => 1,
                    /** コード */
                    'code' => 1,
                    /** 名前 */
                    'name' => 'リーフ',
                ],
                [
                    /** ID */
                    'id' => 2,
                    /** コード */
                    'code' => 2,
                    /** 名前 */
                    'name' => 'ティーバッグ',
                ],
                [
                    /** ID */
                    'id' => 3,
                    /** コード */
                    'code' => 3,
                    /** 名前 */
                    'name' => 'ドリップ',
                ],
                [
                    /** ID */
                    'id' => 4,
                    /** コード */
                    'code' => 4,
                    /** 名前 */
                    'name' => '粉末',
                ],
                [
                    /** ID */
                    'id' => 5,
                    /** コード */
                    'code' => 5,
                    /** 名前 */
                    'name' => 'その他',
                ],
            ]
        );

        DB::statement('SET foreign_key_checks = 1');
    }
}
