<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Models\Master\MasterBranch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 支所マスターテーブル（m_branches） Seeder Class
 */
class MasterBranchesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate
        if (config('seeder.faker.branch.truncate')) {
            DB::statement('SET foreign_key_checks = 0');
            MasterBranch::query()->truncate();
            DB::statement('SET foreign_key_checks = 1');
        }

        // production時は実行しない
        if (app()->isProduction()) {
            return;
        }

        // データ生成
        factory(MasterBranch::class, config('seeder.faker.branch.generate_count'))->create();
    }
}
