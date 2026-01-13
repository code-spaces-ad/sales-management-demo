<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 　部門マスターテーブル（m_departments） Seeder Class
 */
class MasterDepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_departments')->insert(
            [
                [
                    /** ID */
                    'id' => 100,
                    /** コード */
                    'code' => 100,
                    /** 名前 */
                    'name' => '本社',
                    /** 名前ｶﾅ */
                    'name_kana' => 'ﾎﾝｼｬ',
                    /** 略称 */
                    'mnemonic_name' => '本社',
                    /** 責任者 */
                    'manager_id' => 1,
                ],
                [
                    /** ID */
                    'id' => 1000,
                    /** コード */
                    'code' => 1000,
                    /** 名前 */
                    'name' => '卸部',
                    /** 名前ｶﾅ */
                    'name_kana' => 'ｵﾛｼﾌﾞ',
                    /** 略称 */
                    'mnemonic_name' => '卸部',
                    /** 責任者 */
                    'manager_id' => 2,
                ],
                [
                    /** ID */
                    'id' => 2000,
                    /** コード */
                    'code' => 2000,
                    /** 名前 */
                    'name' => '小売部',
                    /** 名前ｶﾅ */
                    'name_kana' => 'ｺｳﾘﾌﾞ',
                    /** 略称 */
                    'mnemonic_name' => '小売部',
                    /** 責任者 */
                    'manager_id' => 3,
                ],
            ]
        );
    }
}
