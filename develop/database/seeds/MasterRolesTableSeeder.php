<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Enums\UserRoleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 権限マスターテーブル（m_roles） Seeder Class
 */
class MasterRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_roles')->insert(
            [
                [
                    /** 権限ID */
                    'id' => UserRoleType::SYS_ADMIN,
                    /** 権限名 */
                    'name' => 'システム管理者',
                ],
                [
                    /** 権限ID */
                    'id' => UserRoleType::SYS_OPERATOR,
                    /** 権限名 */
                    'name' => 'システム運用者',
                ],
                [
                    /** 権限ID */
                    'id' => UserRoleType::EMPLOYEE,
                    /** 権限名 */
                    'name' => '従業員',
                ],
            ]
        );
    }
}
