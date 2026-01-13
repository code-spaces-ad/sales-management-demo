<?php

namespace App\Services\Excel;

use App\Consts\DB\Master\MasterCustomersConst;
use App\Helpers\LogHelper;
use App\Models\Sale\SalesOrder;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesInvoiceCommonService
{
    /** 罫線設定 */
    // 罫線：外->太線／内->微細線
    public static $arrStyleThin = [
        'borders' => [
            'top' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000080'],
            ],
            'right' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000080'],
            ],
            'left' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000080'],
            ],
            'bottom' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000080'],
            ],
        ],
    ];

    // 罫線：外->太線／内->微細線
    public static $arrStyleThinInsideHair = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000080'],
            ],
            'inside' => [
                'borderStyle' => Border::BORDER_HAIR,
                'color' => ['rgb' => '000080'],
            ],
        ],
    ];

    // 罫線：外->細線／内->微細線
    public static $arrStyleThickInsideHair = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THICK,
                'color' => ['rgb' => '000080'],
            ],
            'inside' => [
                'borderStyle' => Border::BORDER_HAIR,
                'color' => ['rgb' => '000080'],
            ],
        ],
    ];

    // 罫線：全て->微細線
    public static $arrStyleAllHair = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_HAIR,
                'color' => ['rgb' => '000080'],
            ],
            'inside' => [
                'borderStyle' => Border::BORDER_HAIR,
                'color' => ['rgb' => '000080'],
            ],
        ],
    ];

    // 罫線：外->微細線／内->SLANTDASHDOT
    public static $arrStyleHairInsideDot = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_HAIR,
                'color' => ['rgb' => '000080'],
            ],
            'inside' => [
                'borderStyle' => Border::BORDER_SLANTDASHDOT,
                'color' => ['rgb' => '000080'],
            ],
        ],
    ];

    /**
     * [DEBUG]メモリ使用量出力
     *
     * @param string $prefix
     * @return void
     */
    public static function echo_mem(string $prefix = ''): void
    {
        LogHelper::info(__CLASS__, '[' . $prefix . ']', (floor(memory_get_usage() / 1024 / 1024) . 'MB' . PHP_EOL));
    }

    /**
     * [DEBUG]マルチバイトを含む文字埋め
     *
     * @param $input
     * @param $pad_length
     * @param $pad_string
     * @param $pad_style
     * @param $encoding
     * @return string
     */
    public static function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_style = STR_PAD_RIGHT, $encoding = 'UTF-8'): string
    {
        $mb_pad_length = strlen($input) - mb_strlen($input, $encoding) + $pad_length;

        return str_pad($input, $mb_pad_length, $pad_string, $pad_style);
    }

    /**
     * ■入金明細の内訳を配列で返す
     *
     * @param SalesOrder $sales_order
     * @return array[]
     */
    public static function getDepositPaymentDetail1(SalesOrder $sales_order): array
    {
        return
            [
                ['amount' => $sales_order->amount_cash,
                    'note' => $sales_order->note_cash, 'number' => null, 'date' => null],
                ['amount' => $sales_order->amount_check,
                    'note' => $sales_order->note_check, 'number' => null, 'date' => null],
                ['amount' => $sales_order->amount_transfer,
                    'note' => $sales_order->note_transfer, 'number' => null, 'date' => null],
                ['amount' => $sales_order->amount_bill,
                    'note' => $sales_order->note_bill, 'number' => $sales_order->bill_number,
                    'date' => $sales_order->bill_date],
                ['amount' => $sales_order->amount_offset,
                    'note' => $sales_order->note_offset, 'number' => null, 'date' => null],
                ['amount' => $sales_order->amount_discount,
                    'note' => $sales_order->note_discount, 'number' => null, 'date' => null],
                ['amount' => $sales_order->amount_fee,
                    'note' => $sales_order->note_fee, 'number' => null, 'date' => null],
                ['amount' => $sales_order->amount_other,
                    'note' => $sales_order->note_other, 'number' => null, 'date' => null],
            ];
    }

    /**
     * 編集対象のシート取得
     *
     * @param Spreadsheet $spreadsheet
     * @param Worksheet $sheet
     * @param int $customer_id
     * @return Worksheet
     */
    public static function getActiveSheet(Spreadsheet $spreadsheet,
        Worksheet $sheet,
        int $customer_id): Worksheet
    {
        $sheet_name =
            str_pad($customer_id, MasterCustomersConst::CODE_MAX_LENGTH, '0', STR_PAD_LEFT);
        $sheet->setTitle($sheet_name);

        // 編集対象のシート取得
        return $spreadsheet->getSheetByName($sheet_name);
    }

    /**
     * 印刷範囲の設定
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @param int $sales_invoice_printing_method
     * @return void
     *
     * @throws Exception
     */
    public static function setPagePrintArea(Worksheet $sheet,
        int $current_row,
        int $sales_invoice_printing_method): void
    {
        // 出力サイズ
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        // 出力方向
        $print_direction = $sales_invoice_printing_method == 1 ? PageSetup::ORIENTATION_LANDSCAPE : PageSetup::ORIENTATION_PORTRAIT;
        $sheet->getPageSetup()->setOrientation($print_direction);
        // 左右の中央揃え
        $sheet->getPageSetup()->setHorizontalCentered(true);
        // 余白
        $objPageMargins = $sheet->getPageMargins();
        $objPageMargins->setTop(0.5)->setRight(0.17)->setLeft(0.15)->setBottom(0.5)->setHeader(0.2);
        // 出力レイアウト
        $sheet->getPageSetup()->setPrintArea("A1:BQ$current_row");

        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
    }
}
