<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 毎日00:00にDBをリフレッシュ
        $schedule->command('db:fresh-database-daily')
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->environments(['production']);

        // // DBバックアップを実行 (laravel-backup)
        // $schedule->command('backup:run --only-db')
        //     ->dailyAt(config('backup.backup.backup_time'))
        //     ->environments(['production']);

        // // DBバックアップをクリーンアップ (laravel-backup)
        // $schedule->command('backup:clean')
        //     ->dailyAt(config('backup.backup.cleanup_time'))
        //     ->environments(['production']);

        // // 現在庫データ登録
        // self::registInventoryDataClosing($schedule);

        // // POS仕入データ連携
        // self::receivePurchase($schedule);

        // // POS棚卸データ連携
        // self::receiveInventory($schedule);

        // POS販売データ連携
        //        self::receiveSales($schedule);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    // /**
    //  * 現在庫データ登録
    //  *
    //  * @param Schedule $schedule
    //  * @return void
    //  */
    // protected function registInventoryDataClosing(Schedule $schedule): void
    // {
    //     $schedule->command('update_or_insert:inventory_data_closing')
    //         ->monthly()
    //         ->onSuccess(function () {
    //             Log::info('現在庫データを締在庫数へ登録成功');
    //         })
    //         ->onFailure(function () {
    //             Log::error('現在庫データを締在庫数へ登録失敗');
    //         })
    //         ->environments(['staging', 'production']);
    // }

    // /**
    //  * POS仕入データ連携
    //  *
    //  * @param Schedule $schedule
    //  * @return void
    //  */
    // protected function receivePurchase(Schedule $schedule): void
    // {
    //     $schedule->command('run:pos_receive_purchase')
    //         ->dailyAt('22:00')
    //         ->before(function () {
    //             Log::info('========================================');
    //             Log::info('スケジュール：仕入データ連携 開始');
    //         })
    //         ->onSuccess(function () {
    //             Log::info('スケジュール：仕入データの連携 成功');
    //             Log::info('========================================');
    //         })
    //         ->onFailure(function () {
    //             Log::error('スケジュール：仕入データの連携 失敗');
    //             Log::info('========================================');
    //         })
    //         ->environments(['local', 'staging']);
    //     //            ->environments(['staging', 'production']);
    //     // todo 本番一時停止
    // }

    // /**
    //  * POS棚卸データ連携
    //  *
    //  * @param Schedule $schedule
    //  * @return void
    //  */
    // protected function receiveInventory(Schedule $schedule): void
    // {
    //     $schedule->command('run:pos_receive_inventory')
    //         ->dailyAt('23:00')
    //         ->before(function () {
    //             Log::info('========================================');
    //             Log::info('スケジュール：棚卸データ連携 開始');
    //         })
    //         ->onSuccess(function () {
    //             Log::info('スケジュール：棚卸データの連携 成功');
    //             Log::info('========================================');
    //         })
    //         ->onFailure(function () {
    //             Log::error('スケジュール：棚卸データの連携 失敗');
    //             Log::info('========================================');
    //         })
    //         ->environments(['local', 'staging']);
    //     //            ->environments(['staging', 'production']);
    //     // todo 本番一時停止
    // }

    // /**
    //  * POS販売データ連携
    //  *
    //  * @param Schedule $schedule
    //  * @return void
    //  */
    // protected function receiveSales(Schedule $schedule): void
    // {
    //     $schedule->command('run:pos_receive_order')
    //         ->everyThirtyMinutes()->between('06:00', '23:00')
    //         ->before(function () {
    //             Log::info('========================================');
    //             Log::info('スケジュール：販売データ連携 開始');
    //         })
    //         ->onSuccess(function () {
    //             Log::info('スケジュール：販売データの連携 成功');
    //             Log::info('========================================');
    //         })
    //         ->onFailure(function () {
    //             Log::error('スケジュール：販売データの連携 失敗');
    //             Log::info('========================================');
    //         })
    //         ->environments(['local']);
    //     //            ->environments(['local', 'staging']);
    //     //            ->environments(['staging', 'production']);
    //     // todo ステージング・本番一時停止
    // }
}
