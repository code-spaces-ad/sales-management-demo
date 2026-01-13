<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Tempファイル削除 コマンドクラス
 */
class StorageDeleteTemp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:delete-temp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete temp files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Excelファイル削除
        $this->deleteExcelFile();
        // PDFファイル削除
        $this->deletePdfFile();
    }

    /**
     * Excelファイル削除処理
     *
     * @return void
     */
    private function deleteExcelFile()
    {
        $key_timestamp = strtotime(config('consts.excel.temp_deletion_period'));

        $path = storage_path(config('consts.excel.temp_path'));
        if (!\File::isDirectory($path)) {
            echo 'Excelフォルダが存在しません。パス：' . $path . PHP_EOL;

            return;
        }

        $files = \File::files($path);
        foreach ($files as $file) {
            if ($file->getExtension() != 'xlsx') {
                // Excel以外はスキップ
                continue;
            }

            $timestamp = \File::lastModified($file);    // 最終更新日時
            if ($key_timestamp >= $timestamp) {
                // 指定期間以前であれば、対象Excelファイルを削除
                \File::delete($file);   // ※エラーは無視される
            }
        }
    }

    /**
     * PDFファイル削除処理
     *
     * @return void
     */
    private function deletePdfFile()
    {
        $key_timestamp = strtotime(config('consts.pdf.temp_deletion_period'));

        $path = public_path(config('consts.pdf.temp_path'));
        if (!\File::isDirectory($path)) {
            echo 'PDFフォルダが存在しません。パス：' . $path . PHP_EOL;

            return;
        }

        $files = \File::files($path);
        foreach ($files as $file) {
            if ($file->getExtension() != 'pdf') {
                // PDF以外はスキップ
                continue;
            }

            $timestamp = \File::lastModified($file);    // 最終更新日時
            if ($key_timestamp >= $timestamp) {
                // 指定期間以前であれば、対象PDFファイルを削除
                \File::delete($file);   // ※エラーは無視される
            }
        }
    }
}
