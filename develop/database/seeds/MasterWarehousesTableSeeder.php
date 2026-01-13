<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 倉庫マスターテーブル（m_warehouses） Seeder Class
 */
class MasterWarehousesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_warehouses')->insert(
            [
                [
                    /** ID */
                    'id' => 1,
                    /** コード */
                    'code' => 100,
                    /** 名前 */
                    'name' => '倉庫1',
                    /** 名前かな */
                    'name_kana' => 'ソウコイチ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 2,
                    /** コード */
                    'code' => 1010,
                    /** 名前 */
                    'name' => '倉庫2',
                    /** 名前かな */
                    'name_kana' => 'ソウコニ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 3,
                    /** コード */
                    'code' => 1020,
                    /** 名前 */
                    'name' => '倉庫３',
                    /** 名前かな */
                    'name_kana' => 'ソウコサン',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 4,
                    /** コード */
                    'code' => 1030,
                    /** 名前 */
                    'name' => '倉庫4',
                    /** 名前かな */
                    'name_kana' => 'ソウコヨン',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 5,
                    /** コード */
                    'code' => 2010,
                    /** 名前 */
                    'name' => '倉庫5',
                    /** 名前かな */
                    'name_kana' => 'ソウコゴ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 6,
                    /** コード */
                    'code' => 2020,
                    /** 名前 */
                    'name' => '倉庫6',
                    /** 名前かな */
                    'name_kana' => 'ソウコロク',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 7,
                    /** コード */
                    'code' => 2030,
                    /** 名前 */
                    'name' => '倉庫7',
                    /** 名前かな */
                    'name_kana' => 'ソウコナナ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 8,
                    /** コード */
                    'code' => 2040,
                    /** 名前 */
                    'name' => '倉庫8',
                    /** 名前かな */
                    'name_kana' => 'ソウコハチ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 9,
                    /** コード */
                    'code' => 2050,
                    /** 名前 */
                    'name' => '倉庫9',
                    /** 名前かな */
                    'name_kana' => 'ソウコキュウ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 10,
                    /** コード */
                    'code' => 2060,
                    /** 名前 */
                    'name' => '倉庫10',
                    /** 名前かな */
                    'name_kana' => 'ソウコジュウ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 11,
                    /** コード */
                    'code' => 2090,
                    /** 名前 */
                    'name' => '倉庫11',
                    /** 名前かな */
                    'name_kana' => 'ソウコジュウイチ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 12,
                    /** コード */
                    'code' => 2100,
                    /** 名前 */
                    'name' => '倉庫12',
                    /** 名前かな */
                    'name_kana' => 'ソウコジュウニ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 13,
                    /** コード */
                    'code' => 2130,
                    /** 名前 */
                    'name' => '倉庫13',
                    /** 名前かな */
                    'name_kana' => 'ソウコジュウサン',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 14,
                    /** コード */
                    'code' => 2140,
                    /** 名前 */
                    'name' => '国内営業',
                    /** 名前かな */
                    'name_kana' => 'ｺｸﾅｲｴｲｷﾞｮｳ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 15,
                    /** コード */
                    'code' => 2150,
                    /** 名前 */
                    'name' => '海外営業',
                    /** 名前かな */
                    'name_kana' => 'ｶｲｶﾞｲｴｲｷﾞｮｳ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 1,
                ],
                [
                    /** ID */
                    'id' => 65533,
                    /** コード */
                    'code' => 65533,
                    /** 名前 */
                    'name' => '在庫調整',
                    /** 名前かな */
                    'name_kana' => 'ざいこちょうせい',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 0,
                ],
                [
                    /** ID */
                    'id' => 65534,
                    /** コード */
                    'code' => 65534,
                    /** 名前 */
                    'name' => '仕入',
                    /** 名前かな */
                    'name_kana' => 'しいれ',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 0,
                ],
                [
                    /** ID */
                    'id' => 65535,
                    /** コード */
                    'code' => 65535,
                    /** 名前 */
                    'name' => '納品',
                    /** 名前かな */
                    'name_kana' => 'のうひん',
                    /** 在庫管理有無(しない:0、する:1) */
                    'is_control_inventory' => 0,
                ],
            ]
        );
    }
}
