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
class UpdateMasterUsersTableSeederToAddUnknownUser extends Seeder
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
                    'id' => 65535,
                    /** コード */
                    'code' => 65535,
                    /** ログインID */
                    'login_id' => 'unknown',
                    /** 名前 */
                    'name' => '不明なユーザー',
                    /** 社員ID */
                    'employee_id' => 99999,
                    /** 権限 */
                    'role_id' => UserRoleType::SUPPLIER,     // とりあえず、一番下の権限
                    /** パスワード */
                    'password' => Hash::make('create7706'),
                    /** メールアドレス */
                    'email' => 'test@test.com',
                    /** 備考 */
                    'note' => "テスト用アカウント\r\nパスワード：create7706\r\n",
                ],
            ]
        );
    }
}
