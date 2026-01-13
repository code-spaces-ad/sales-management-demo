<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Models\Master\MasterCustomer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 得意先マスターテーブル（m_customers） Seeder Class
 */
class MasterCustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        if (config('seeder.faker.customer.truncate')) {
            DB::statement('SET foreign_key_checks = 0');
            MasterCustomer::query()->truncate();
            DB::statement('SET foreign_key_checks = 1');
        }

        // production時は実行しない
        if (app()->isProduction()) {
            return;
        }

        // データ生成
        DB::table('m_customers')->insert(
            [
                [
                    /** ID */
                    'id' => 101,
                    /** コード */
                    'code' => 101,
                    /** 担当者ID */
                    'employee_id' => 1,
                    /** 名前 */
                    'name' => 'テスト得意先1',
                    /** 住所1 */
                    'address1' => '宮崎県',
                    /** 請求先ID */
                    'billing_customer_id' => 101,
                    /** 税額端数処理 */
                    'tax_rounding_method_id' => '3',
                    /** 請求締日 */
                    'closing_date' => '0',
                    /** ソートコード */
                    'sort_code' => '101',
                ],
                [
                    /** ID */
                    'id' => 102,
                    /** コード */
                    'code' => 102,
                    /** 担当者ID */
                    'employee_id' => 1,
                    /** 名前 */
                    'name' => 'テスト得意先2',
                    /** 住所1 */
                    'address1' => '宮崎県',
                    /** 請求先ID */
                    'billing_customer_id' => 102,
                    /** 税額端数処理 */
                    'tax_rounding_method_id' => '3',
                    /** 請求締日 */
                    'closing_date' => '0',
                    /** ソートコード */
                    'sort_code' => '102',
                ],
                [
                    /** ID */
                    'id' => 103,
                    /** コード */
                    'code' => 103,
                    /** 担当者ID */
                    'employee_id' => 2,
                    /** 名前 */
                    'name' => 'テスト得意先3',
                    /** 住所1 */
                    'address1' => '宮崎県',
                    /** 請求先ID */
                    'billing_customer_id' => 103,
                    /** 税額端数処理 */
                    'tax_rounding_method_id' => '3',
                    /** 請求締日 */
                    'closing_date' => '0',
                    /** ソートコード */
                    'sort_code' => '103',
                ],
                [
                    /** ID */
                    'id' => 104,
                    /** コード */
                    'code' => 104,
                    /** 担当者ID */
                    'employee_id' => 3,
                    /** 名前 */
                    'name' => 'テスト得意先4',
                    /** 住所1 */
                    'address1' => '宮崎県',
                    /** 請求先ID */
                    'billing_customer_id' => 104,
                    /** 税額端数処理 */
                    'tax_rounding_method_id' => '3',
                    /** 請求締日 */
                    'closing_date' => '0',
                    /** ソートコード */
                    'sort_code' => '104',
                ],
                [
                    /** ID */
                    'id' => 109,
                    /** コード */
                    'code' => 109,
                    /** 担当者ID */
                    'employee_id' => 5,
                    /** 名前 */
                    'name' => 'テスト得意先5',
                    /** 住所1 */
                    'address1' => '宮崎県',
                    /** 請求先ID */
                    'billing_customer_id' => 109,
                    /** 税額端数処理 */
                    'tax_rounding_method_id' => '3',
                    /** 請求締日 */
                    'closing_date' => '0',
                    /** ソートコード */
                    'sort_code' => '109',
                ],
            ]
        );
    }
}
