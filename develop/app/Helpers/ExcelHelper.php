<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Excel用ヘルパークラス
 */
class ExcelHelper
{
    /**
     * Excel出力
     *
     * @param Spreadsheet $spreadsheet
     * @param string $filename
     * @return StreamedResponse
     */
    public static function outputExcel(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        return new StreamedResponse(
            function () use ($spreadsheet) {
                (new Xlsx($spreadsheet))->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment;filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
