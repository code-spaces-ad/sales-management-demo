<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Enums\UserRoleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 取引種別マスターテーブル（m_transaction_types） Seeder Class
 */
class MasterTransactionTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_transaction_types')->insert(
            [
                [
                    /** 取引種別ID */
                    'id' => 1,
                    /** 取引種別名 */
                    'name' => '現売',
                ],
                [
                    /** 取引種別ID */
                    'id' => 2,
                    /** 取引種別名 */
                    'name' => '掛売',
                ],
            ]
        );
    }
}
