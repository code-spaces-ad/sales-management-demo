<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Enums\UserRoleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 敬称マスターテーブル（m_honorific_titles） Seeder Class
 */
class MasterHonorificTitlesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_honorific_titles')->insert(
            [
                [
                    /** 敬称ID */
                    'id' => 1,
                    /** 敬称コード */
                    'code' => 1,
                    /** 敬称名 */
                    'name' => '御中',
                ],
                [
                    /** 敬称ID */
                    'id' => 2,
                    /** 敬称コード */
                    'code' => 2,
                    /** 敬称名 */
                    'name' => '様',
                ],
                [
                    /** 敬称ID */
                    'id' => 3,
                    /** 敬称コード */
                    'code' => 3,
                    /** 敬称名 */
                    'name' => '殿',
                ],
            ]
        );
    }
}
