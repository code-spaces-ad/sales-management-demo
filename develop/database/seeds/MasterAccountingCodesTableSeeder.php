<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ユーザーマスターテーブル（m_users） Seeder Class
 */
class MasterAccountingCodesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_accounting_codes')->insert(
            [
                [
                    /** ID */
                    'id' => 1141,
                    /** コード */
                    'code' => 1141,
                    /** 名前 */
                    'name' => '売掛金',
                    /** 出力対象グループ */
                    'output_group' => 0,
                ],
                [
                    /** ID */
                    'id' => 2103,
                    /** コード */
                    'code' => 2103,
                    /** 名前 */
                    'name' => '買掛金',
                    /** 出力対象グループ */
                    'output_group' => 0,
                ],
                [
                    /** ID */
                    'id' => 4202,
                    /** コード */
                    'code' => 4202,
                    /** 名前 */
                    'name' => '茶仕入高',
                    /** 出力対象グループ */
                    'output_group' => 1,
                ],
                [
                    /** ID */
                    'id' => 4203,
                    /** コード */
                    'code' => 4203,
                    /** 名前 */
                    'name' => 'その他仕入',
                    /** 出力対象グループ */
                    'output_group' => 1,
                ],
                [
                    /** ID */
                    'id' => 4205,
                    /** コード */
                    'code' => 4205,
                    /** 名前 */
                    'name' => '荷造包装費',
                    /** 出力対象グループ */
                    'output_group' => 1,
                ],
                [
                    /** ID */
                    'id' => 4207,
                    /** コード */
                    'code' => 4207,
                    /** 名前 */
                    'name' => '委託費',
                    /** 出力対象グループ */
                    'output_group' => 1,
                ],
            ]
        );
    }
}
