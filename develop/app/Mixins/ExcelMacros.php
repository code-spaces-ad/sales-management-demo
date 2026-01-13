<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Mixins;

use Carbon\Carbon;
use Closure;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Excel Macros
 */
class ExcelMacros
{
    public function exportExcel(): Closure
    {
        return function ($filename = '', $headings = [], $filters = [], $start_row = 1) {
            $now = Carbon::now()->format('YmdHis');
            if (empty($filename)) {
                // デフォルトファイル名
                $table = $this->first()->getTable();
                $filename = $table . '_' . $now . '.xlsx';
            } else {
                // ファイル名の接頭に日付追加
                $filename = $now . '_' . $filename;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getPageSetup()->setPaperSize(config('consts.excel.default_papersize'));
            $sheet->getPageSetup()->setOrientation(config('consts.excel.default_orientation'));
            $sheet->getPageSetup()->setHorizontalCentered(true);
            $sheet->getPageSetup()->setScale(100);

            $excel_data = [];
            if (!empty($headings)) {
                // 空行追加（1以上の場合のみ）
                for ($i = 1; $i < $start_row; ++$i) {
                    $excel_data[] = [];
                }
                // ヘッダー行追加
                $excel_data[] = $headings;
            }

            $this->each(function ($item, $key) use (&$excel_data, $filters) {
                $row_data = null;

                if (empty($filters)) {
                    // フィルターなし
                    $excel_data[] = $item;
                    if (gettype($item) !== 'array') {
                        $row_data = array_values($item->toArray());
                        $excel_data[] = $row_data;
                    }
                } else {
                    // フィルターあり
                    foreach ($filters as $filter) {
                        if (is_callable($filter)) {
                            $row_data[] = $filter($item, $key);
                        }
                    }

                    $excel_data[] = $row_data;
                }
            });
            $sheet->fromArray($excel_data, null, 'A1');

            // Styles
            $last_col = $sheet->getHighestColumn();
            $last_row = $sheet->getHighestRow();
            $last_cell = $last_col . $last_row;

            // Header alignment and background
            $sheet->getStyle("A{$start_row}:{$last_col}{$start_row}")->applyFromArray(
                [
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FF54C754',
                        ],
                    ],
                ]
            );

            // 金額出力セル（B列からI列）を右詰めに設定
            $sheet->getStyle('B:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // 罫線
            $sheet->getStyle("A{$start_row}:{$last_cell}")->applyFromArray(
                [
                    'borders' => [
                        'allBorders' => [
                            /** 罫線スタイル */
                            'borderStyle' => Border::BORDER_THIN,   // 通常線
                            /** 罫線色 */
                            'color' => [
                                'argb' => 'FF000000',
                            ],
                        ],
                    ],
                ]
            );

            $callback = function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            };

            $status = 200;
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment;filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ];

            return new StreamedResponse($callback, $status, $headers);
        };
    }
}
