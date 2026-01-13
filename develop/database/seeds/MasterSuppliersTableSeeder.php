<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Models\Master\MasterSupplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 仕入先マスターテーブル（m_suppliers） Seeder Class
 */
class MasterSuppliersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        if (config('seeder.faker.supplier.truncate')) {
            DB::statement('SET foreign_key_checks = 0');
            MasterSupplier::query()->truncate();
            DB::statement('SET foreign_key_checks = 1');
        }

        // production時は実行しない
        if (app()->isProduction()) {
            return;
        }

        // データ生成
        DB::table('m_suppliers')->insert(
            [
                [
                    /** ID */
                    'id' => 1010,
                    /** コード */
                    'code' => 1010,
                    /** 名前 */
                    'name' => '仕入先1',
                    /** 住所1 */
                    'address1' => '宮崎県',
                    /** 税額端数処理 */
                    'tax_rounding_method_id' => '3',
                    /** 支払締日 */
                    'closing_date' => '0',
                ],
                [
                    /** ID */
                    'id' => 1011,
                    /** コード */
                    'code' => 1011,
                    /** 名前 */
                    'name' => '仕入先2',
                    /** 住所1 */
                    'address1' => '宮崎県',
                    /** 税額端数処理 */
                    'tax_rounding_method_id' => '3',
                    /** 支払締日 */
                    'closing_date' => '0',
                ],
                [
                    /** ID */
                    'id' => 1012,
                    /** コード */
                    'code' => 1012,
                    /** 名前 */
                    'name' => '仕入先3',
                    /** 住所1 */
                    'address1' => '宮崎県',
                    /** 税額端数処理 */
                    'tax_rounding_method_id' => '3',
                    /** 支払締日 */
                    'closing_date' => '0',
                ],
                [
                    /** ID */
                    'id' => 1036,
                    /** コード */
                    'code' => 1036,
                    /** 名前 */
                    'name' => '仕入先4',
                    /** 住所1 */
                    'address1' => '宮崎県',
                    /** 税額端数処理 */
                    'tax_rounding_method_id' => '3',
                    /** 支払締日 */
                    'closing_date' => '0',
                ],
                [
                    /** ID */
                    'id' => 1037,
                    /** コード */
                    'code' => 1037,
                    /** 名前 */
                    'name' => '仕入先5',
                    /** 住所1 */
                    'address1' => '宮崎県',
                    /** 税額端数処理 */
                    'tax_rounding_method_id' => '3',
                    /** 支払締日 */
                    'closing_date' => '0',
                ],
            ]
        );
    }
}
