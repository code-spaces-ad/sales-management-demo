<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Settings Seeder Class
 */
class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->truncate();
        DB::table('settings')->insert(
            [
                [
                    'id' => 1,
                    'key' => 'demo',
                    'group' => 'mode',
                    'value' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'key' => 'send_mail',
                    'group' => 'notification',
                    'value' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 3,
                    'key' => 'send_error_mail',
                    'group' => 'notification',
                    'value' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 4,
                    'key' => 'send_error_teams',
                    'group' => 'notification',
                    'value' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 5,
                    'key' => 'error_teams_webhook_url',
                    'group' => 'notification',
                    'value' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 6,
                    'key' => 'send_login_teams',
                    'group' => 'notification',
                    'value' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 7,
                    'key' => 'login_teams_webhook_url',
                    'group' => 'notification',
                    'value' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 8,
                    'key' => 'bank_transfer_fee_target_office_facilities',
                    'group' => 'report',
                    'value' => '2,3,4,10',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 9,
                    'key' => 'bank_transfer_fee_sort',
                    'group' => 'report',
                    'value' => '1010,1030,1020,2060',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 10,
                    'key' => 'bank_transfer_fee_replace_blank',
                    'group' => 'report',
                    'value' => '営業所,営業,所,部',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]
        );
    }
}
