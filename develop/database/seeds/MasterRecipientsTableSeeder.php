<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Models\Master\MasterRecipient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 納品先マスターテーブル（m_recipients） Seeder Class
 */
class MasterRecipientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        if (config('seeder.faker.recipient.truncate')) {
            DB::statement('SET foreign_key_checks = 0');
            MasterRecipient::query()->truncate();
            DB::statement('SET foreign_key_checks = 1');
        }

        // production時は実行しない
        if (app()->isProduction()) {
            return;
        }

        // データ生成
        factory(MasterRecipient::class, config('seeder.faker.recipient.generate_count'))->create();
    }
}
