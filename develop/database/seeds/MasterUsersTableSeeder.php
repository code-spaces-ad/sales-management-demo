<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Enums\UserRoleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ユーザーマスターテーブル（m_users） Seeder Class
 */
class MasterUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('m_users')->insert(
            [
                [
                    /** ID */
                    'id' => 1,
                    /** コード */
                    'code' => 1,
                    /** ログインID */
                    'login_id' => 'sysadmin',
                    /** 名前 */
                    'name' => 'システム管理者',
                    /** 社員ID */
                    'employee_id' => 60,
                    /** 権限 */
                    'role_id' => UserRoleType::SYS_ADMIN,     // 1：システム管理者
                    /** パスワード */
                    'password' => Hash::make('password'),
                    /** リメンバートークン */
                    'remember_token' => null,
                    /** メールアドレス */
                    'email' => 'test@test.com',
                    /** 備考 */
                    'note' => "システム管理者",
                ],
                [
                    /** ID */
                    'id' => 2,
                    /** コード */
                    'code' => 2,
                    /** ログインID */
                    'login_id' => 'sales-management',
                    /** 名前 */
                    'name' => 'システム運用者',
                    /** 社員ID */
                    'employee_id' => 60,
                    /** 権限 */
                    'role_id' => UserRoleType::SYS_OPERATOR,     // 1：システム運用者
                    /** パスワード */
                    'password' => Hash::make('password'),
                    /** リメンバートークン */
                    'remember_token' => null,
                    /** メールアドレス */
                    'email' => 'operator@example.com',
                    /** 備考 */
                    'note' => "CodeSpaces 管理者用アカウント\r\nパスワード：password\r\n",
                ],
                [
                    /** ID */
                    'id' => 3,
                    /** コード */
                    'code' => 3,
                    /** ログインID */
                    'login_id' => 'test_user1',
                    /** 名前 */
                    'name' => 'テストユーザー',
                    /** 社員ID */
                    'employee_id' => 60,
                    /** 権限 */
                    'role_id' => UserRoleType::EMPLOYEE,
                    /** パスワード */
                    'password' => Hash::make('password'),
                    /** リメンバートークン */
                    'remember_token' => null,
                    /** メールアドレス */
                    'email' => 'user1@example.com',
                    /** 備考 */
                    'note' => null,
                ],
                [
                    /** ID */
                    'id' => 65535,
                    /** コード */
                    'code' => 65535,
                    /** ログインID */
                    'login_id' => 'unknown',
                    /** 名前 */
                    'name' => 'システム構築用アカウント',
                    /** 社員ID */
                    'employee_id' => 60,
                    /** 権限 */
                    'role_id' => UserRoleType::EMPLOYEE,     // とりあえず、一番下の権限
                    /** パスワード */
                    'password' => Hash::make('create7706'),
                    /** リメンバートークン */
                    'remember_token' => null,
                    /** メールアドレス */
                    'email' => 'create@example.com',
                    /** 備考 */
                    'note' => "システム構築用アカウント",
                ],
            ]
        );
    }
}
