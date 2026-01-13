<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 集計グループマスターテーブル（m_summary_group） Seeder Class
 */
class MasterSummaryGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_summary_group')->insert(
            [
                [
                    /** ID */
                    'id' => 1,
                    /** コード */
                    'code' => 10,
                    /** 名前 */
                    'name' => '集計グループ1',
                    /** 名前かな */
                    'note' => null,
                ],
                [
                    /** ID */
                    'id' => 2,
                    /** コード */
                    'code' => 11,
                    /** 名前 */
                    'name' => '集計グループ2',
                    /** 名前かな */
                    'note' => null,
                ],
                [
                    /** ID */
                    'id' => 3,
                    /** コード */
                    'code' => 12,
                    /** 名前 */
                    'name' => '集計グループ3',
                    /** 名前かな */
                    'note' => null,
                ],
            ]
        );
    }
}
