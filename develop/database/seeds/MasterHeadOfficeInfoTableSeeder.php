<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 会社情報マスターテーブル（m_head_office_information） Seeder Class
 */
class MasterHeadOfficeInfoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_head_office_information')->insert(
            [
                [
                    'id' => 1,
                    'company_name' => 'CodeSpaces',
                    'representative_name' => '泰 圭介',
                    'postal_code1' => '880',
                    'postal_code2' => '0036',
                    'address1' => '宮崎県宮崎市',
                    'tel_number' => '0123-456-7890',
                    'tel_number2' => '',
                    'fax_number' => '',
                    'invoice_number' => 'T0123456789012',
                    'bank_account1' => '',
                    'bank_account2' => '',
                    'bank_account3' => '',
                ]
            ]
        );
    }
}
