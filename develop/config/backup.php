<?php

/**
 * @copyright © 2025 CodeSpaces
 */

return [

    'backup' => [

        /*
         * このアプリケーションの名前。この名前を使用して監視できます
         * バックアップ
         * The name of this application. You can use this name to monitor
         * the backups.
         */
        'name' => env('BACKUP_NAME', 'laravel-backup'),

        'source' => [

            'files' => [

                /*
                 * バックアップに含まれるディレクトリとファイルのリスト。
                 * The list of directories and files that will be included in the backup.
                 */
                'include' => [
                    base_path(),
                ],

                /*
                 * これらのディレクトリとファイルはバックアップから除外されます。
                 * These directories and files will be excluded from the backup.
                 *
                 * バックアッププロセスで使用されるディレクトリは自動的に除外されます。
                 * Directories used by the backup process will automatically be excluded.
                 */
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],

                /*
                 * シンボリックリンクをたどる必要があるかどうかを決定します。
                 * Determines if symlinks should be followed.
                 */
                'follow_links' => false,

                /*
                 * 読み取り不可能なフォルダを回避する必要があるかどうかを決定します。
                 * Determines if it should avoid unreadable folders.
                 */
                'ignore_unreadable_directories' => false,

                'relative_path' => '',
            ],

            /*
             * バックアップする必要のあるデータベースへの接続の名前
             * MySQL、PostgreSQL、SQLite、Mongoデータベースがサポートされています。
             * The names of the connections to the databases that should be backed up
             * MySQL, PostgreSQL, SQLite and Mongo databases are supported.
             *
             * The content of the database dump may be customized for each connection
             * by adding a 'dump' key to the connection settings in config/database.php.
             * E.g.
             * 'mysql' => [
             *       ...
             *      'dump' => [
             *           'excludeTables' => [
             *                'table_to_exclude_from_backup',
             *                'another_table_to_exclude'
             *            ]
             *       ],
             * ],
             *
             * If you are using only InnoDB tables on a MySQL server, you can
             * also supply the useSingleTransaction option to avoid table locking.
             *
             * E.g.
             * 'mysql' => [
             *       ...
             *      'dump' => [
             *           'useSingleTransaction' => true,
             *       ],
             * ],
             *
             * For a complete list of available customization options, see https://github.com/spatie/db-dumper
             */
            'databases' => [
                'mysql',
            ],
        ],

        /** バックアップ時間 */
        'backup_time' => env('BACKUP_TIME', '01:00'),
        /** クリーンアップ時間 */
        'cleanup_time' => env('CLEANUP_TIME', '01:01'),

        /*
         * データベースダンプを圧縮して、ディスクスペースの使用量を減らすことができます。
         * The database dump can be compressed to decrease diskspace usage.
         *
         * Out of the box Laravel-backup supplies
         * Spatie\DbDumper\Compressors\GzipCompressor::class.
         *
         * You can also create custom compressor. More info on that here:
         * https://github.com/spatie/db-dumper#using-compression
         *
         * If you do not want any compressor at all, set it to null.
         */
        'database_dump_compressor' => null,

        'destination' => [

            /*
             * バックアップzipファイルに使用されるファイル名プレフィックス。
             * The filename prefix used for the backup zip file.
             */
            'filename_prefix' => env('FILENAME_PREFIX', ''),

            /*
             * バックアップが保存されるディスク名。
             * The disk names on which the backups will be stored.
             */
            'disks' => [
                'local',
            ],
        ],

        /*
         * 一時ファイルが保存されるディレクトリ。
         * The directory where the temporary files will be stored.
         */
        'temporary_directory' => storage_path('app/backup-temp'),
    ],

    /*
     * 特定のイベントが発生したときに通知を受け取ることができます。箱から出して、「mail」と「slack」を使用できます。
     * Slackの場合、guzzlehttp / guzzleとlaravel / slack-notification-channelをインストールする必要があります。
     * You can get notified when specific events occur. Out of the box you can use 'mail' and 'slack'.
     * For Slack you need to install guzzlehttp/guzzle and laravel/slack-notification-channel.
     *
     * You can also use your own notification classes, just make sure the class is named after one of
     * the `Spatie\Backup\Events` classes.
     */
    'notifications' => [

        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => ['mail'],
        ],

        /*
         * ここでは、通知の送信先となる通知先を指定できます。デフォルト
         * notizableは、この構成ファイルで指定された変数を使用します。
         * Here you can specify the notifiable to which the notifications should be sent. The default
         * notifiable will use the variables specified in this config file.
         */
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => array_map('trim', explode(',', env('NOTIFICATIONS_MAIL_ADDRESS'))),

            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'Example'),
            ],
        ],

        'slack' => [
            'webhook_url' => '',

            /*
             * これがnullに設定されている場合、Webhookのデフォルトチャネルが使用されます。
             * If this is set to null the default channel of the webhook will be used.
             */
            'channel' => null,

            'username' => null,

            'icon' => null,

        ],
    ],

    /*
     * ここで、監視するバックアップを指定できます。
     * Here you can specify which backups should be monitored.
     * If a backup does not meet the specified requirements the
     * UnHealthyBackupWasFound event will be fired.
     */
    'monitor_backups' => [
        [
            'name' => env('BACKUP_NAME', 'laravel-backup'),
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],

        /*
        [
            'name' => 'name of the second app',
            'disks' => ['local', 's3'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
        */
    ],

    'cleanup' => [
        /*
         *
         * 古いバックアップをクリーンアップするために使用される戦略。デフォルトの戦略
         * すべてのバックアップを一定の日数保持します。その期間の後のみ
         * 毎日のバックアップが保持されます。その期間の後、毎週のバックアップのみが行われます
         * 保持されるなど。
         * The strategy that will be used to cleanup old backups. The default strategy
         * will keep all backups for a certain amount of days. After that period only
         * a daily backup will be kept. After that period only weekly backups will
         * be kept and so on.
         *
         * No matter how you configure it the default strategy will never
         * delete the newest backup.
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [

            /*
             * バックアップを保持する必要がある日数
             * The number of days for which backups must be kept.
             */
            'keep_all_backups_for_days' => 7,

            /*
             * 毎日のバックアップを保持する必要がある日数
             * The number of days for which daily backups must be kept.
             */
            'keep_daily_backups_for_days' => 16,

            /*
             * 1週間のバックアップを保持する必要がある週数
             * The number of weeks for which one weekly backup must be kept.
             */
            'keep_weekly_backups_for_weeks' => 8,

            /*
             * 1か月のバックアップを保持する必要がある月数
             * The number of months for which one monthly backup must be kept.
             */
            'keep_monthly_backups_for_months' => 4,

            /*
             * 1年に1回のバックアップを保持する必要がある年数
             * The number of years for which one yearly backup must be kept.
             */
            'keep_yearly_backups_for_years' => 2,

            /*
             * バックアップをクリーンアップした後、最も古いバックアップを削除するまで
             * このメガバイト数に達しました。
             * After cleaning up the backups remove the oldest backup until
             * this amount of megabytes has been reached.
             */
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],

];
