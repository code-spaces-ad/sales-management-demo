<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * PDF用ヘルパークラス
 */
class PdfHelper
{
    /**
     * Excel -> PDF 変換
     * ※exec()で、LibreOffice で変換
     *
     * @param string $excel_path Excelファイルパス
     * @param string $pdf_dir PDF出力先ディレクトリパス
     * @return int exec() 実行結果
     */
    public static function convertPdf(string $excel_path, string $pdf_dir): int
    {
        $soffice = config('consts.pdf.exe_libre_office');
        $useHome = config('consts.pdf.use_home_option');
        $tmp = $useHome ? 'export HOME=/tmp;' : '';
        $command = "$tmp $soffice --headless --convert-to pdf --outdir $pdf_dir $excel_path";
        exec($command, $out, $ret);
        if ($ret) {
            $msg = 'LibreOffice 変換エラー ret=' . $ret . ', out=' . json_encode($out) . ', command=' . $command;
            Log::error($msg);
        }

        return $ret;
    }

    /**
     * PDFファイル結合（gsコマンド）
     *
     * @param string $org_path 結合元PDF/結合後PDF　ファイル格納パス
     * @param array $files 結合元PDF
     * @param string $out_file_name PDF出力先ディレクトリパス
     * @return void
     */
    public static function joinPdf(string $org_path, array $files, string $out_file_name): void
    {
        try {
            $join_files = '';
            foreach ($files as $file) {
                if ($file != '') {
                    $join_files .= "'" . $org_path . '/' . $file . "'" . ' ';
                }
            }
            $command = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile='$out_file_name' $join_files";
            exec($command, $out, $ret);
        } catch (Exception $ex) {
            $msg = 'PDF 結合(joinPdf)エラー';
            Log::error($msg);
        }
    }
}
