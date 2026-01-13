<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 種別マスターテーブル（m_kinds） Seeder Class
 */
class MasterKindsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET foreign_key_checks = 0');

        DB::table('m_kinds')->insert(
            [
                [
                    /** ID */
                    'id' => 1,
                    /** コード */
                    'code' => 1,
                    /** 名前 */
                    'name' => '製品',
                ],
                [
                    /** ID */
                    'id' => 2,
                    /** コード */
                    'code' => 2,
                    /** 名前 */
                    'name' => '半製品',
                ],
                [
                    /** ID */
                    'id' => 3,
                    /** コード */
                    'code' => 3,
                    /** 名前 */
                    'name' => '原材料',
                ],
                [
                    /** ID */
                    'id' => 4,
                    /** コード */
                    'code' => 4,
                    /** 名前 */
                    'name' => '外注加工費',
                ],
                [
                    /** ID */
                    'id' => 5,
                    /** コード */
                    'code' => 5,
                    /** 名前 */
                    'name' => '切手・印紙類',
                ],
                [
                    /** ID */
                    'id' => 6,
                    /** コード */
                    'code' => 6,
                    /** 名前 */
                    'name' => 'その他',
                ],
                [
                    /** ID */
                    'id' => 7,
                    /** コード */
                    'code' => 7,
                    /** 名前 */
                    'name' => 'セット商品',
                ],
            ]
        );

        DB::statement('SET foreign_key_checks = 1');
    }
}
