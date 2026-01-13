<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Enums\TaxType;
use App\Models\Master\MasterAccountingCode;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterTransactionType;
use App\Models\Sale\SalesOrderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JournalExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 4;

    /** 明細行の終わりの行 */
    protected int $last_row_detail;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.journal')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.journal')
            . $prefix . config('consts.pdf.filename.file_extension');

        parent::__construct($downloadExcelFileName, $downloadPdfFileName);
    }

    /**
     * Excelデータ作成
     *
     * @param array $searchConditions
     * @param array $outputData
     * @param bool $isPdf
     * @return Spreadsheet
     *
     * @throws Exception
     */
    public function getSpreadSheet(array $searchConditions, array $outputData, bool $isPdf = false): Spreadsheet
    {
        // テンプレートファイルの読み込み
        $activeSheet = $this->initSpreadSheet(storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.journal')
        ), PageSetup::ORIENTATION_LANDSCAPE);

        // ヘッダー行の出力
        $this->setHeader($activeSheet, $searchConditions);

        // タイトル行の出力
        $this->setTitle($activeSheet);

        // 明細行の出力
        $this->setDetails($activeSheet, $outputData);

        // 合計行の出力
        $this->setTotal($activeSheet, $outputData);

        // Excelシートの設定
        $this->setPageSetup($activeSheet);

        return $this->spreadSheet;
    }

    /**
     * ヘッダー行の出力
     *
     * @param Worksheet $activeSheet
     * @param array $searchConditions
     * @return void
     */
    private function setHeader(Worksheet $activeSheet, array $searchConditions): void
    {
        // 期間の設定
        if (isset($searchConditions['order_date'])) {
            $startDate = $searchConditions['order_date']['start'] ?? '';
            $endDate = $searchConditions['order_date']['end'] ?? '';

            if ($startDate && $endDate) {
                $dateRange = Carbon::parse($startDate)->format('Y/m/d') . ' 〜 ' . Carbon::parse($endDate)->format('Y/m/d');
                $activeSheet->setCellValue('A1', $dateRange);
            }
        }

        $activeSheet->setCellValue('C1', '＊＊ 仕　訳　帳 ＊＊');
    }

    /**
     * タイトル行の出力
     *
     * @param Worksheet $activeSheet
     * @return void
     */
    private function setTitle(Worksheet $activeSheet): void
    {
        // セルの結合
        $mergeCells = [
            'A2:A3', 'B2:B3', 'C2:C3', 'D2:D3', 'E2:E3', 'F2:F3', 'G2:G3',
            'H2:K2', 'L2:L3',
        ];

        foreach ($mergeCells as $range) {
            $activeSheet->mergeCells($range);
        }

        // カラム幅の設定
        $columnWidths = [
            'A' => 18, 'B' => 10, 'C' => 10, 'D' => 10, 'E' => 11,
            'F' => 10, 'G' => 10, 'H' => 12, 'I' => 11, 'J' => 11, 'K' => 11, 'L' => 9,
        ];

        foreach ($columnWidths as $column => $width) {
            $activeSheet->getColumnDimension($column)->setWidth($width);
        }

        // タイトル行の設定
        $cellValues = [
            'A2' => '事業所名',
            'B2' => '借方科目名',
            'C2' => '借方科目',
            'D2' => '借方補助',
            'E2' => '貸方科目名',
            'F2' => '貸方科目',
            'G2' => '貸方補助',
            'H2' => '金額',
            'H3' => '合計',
            'I3' => '8%',
            'J3' => '10%',
            'K3' => '非課税',
            'L2' => '摘要',
        ];

        foreach ($cellValues as $cell => $value) {
            $activeSheet->setCellValue($cell, $value);
        }

        // スタイルの設定
        $styleRanges = [
            'A2:G3',
            'H2:K3',
            'L2:L3',
        ];

        foreach ($styleRanges as $range) {
            $style = $activeSheet->getStyle($range);
            $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // 下線の二重線設定
        $activeSheet->getStyle('A3:L3')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
    }

    /**
     * 明細データの出力
     *
     * @param Worksheet $activeSheet
     * @param array $outputData DBから取得した明細データ
     * @return void
     */
    private function setDetails(Worksheet $activeSheet, array $outputData): void
    {
        $row = $this->start_row_detail;

        foreach ($outputData as $data) {
            // 事業所名
            $activeSheet->setCellValue("A{$row}", $data['office_facilities_name'] ?? '');

            // 借方科目名
            $activeSheet->setCellValue("B{$row}", $data['accounting_code_name'] ?? '');

            // 借方科目
            $activeSheet->setCellValue("C{$row}", $data['accounting_code_code'] ?? '');

            // 借方補助　※事業所IDを補助科目IDに変換
            $officeFacilitiesMapping = [
                5 => 1,
                9 => 5,
                10 => 6,
                11 => 9,
                12 => 10,
                14 => 12,
            ];
            $activeSheet->setCellValue("D{$row}", $officeFacilitiesMapping[$data['office_facilities_id']] ?? '');

            // 貸方科目名
            $activeSheet->setCellValue("E{$row}", '売上高');

            // 貸方科目
            $activeSheet->setCellValue("F{$row}", '4101');

            // 金額合計
            $activeSheet->setCellValue("H{$row}", $data['total_sub_total'] ?? 0);

            // 8%（軽減税率）
            $activeSheet->setCellValue("I{$row}", $data['total_reduced_tax_sub_total'] ?? 0);

            // 10%（標準税率）
            $activeSheet->setCellValue("J{$row}", $data['total_regular_tax_sub_total'] ?? 0);

            // 非課税
            $activeSheet->setCellValue("K{$row}", $data['total_free_tax_sub_total'] ?? 0);

            // スタイルの設定
            $activeSheet->getStyle("A{$row}:L{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // 数値セルの書式設定
            $activeSheet->getStyle("H{$row}:K{$row}")->getNumberFormat()->setFormatCode('#,##0');

            // 営業推進部の場合、2行目を追加
            if ($data['office_facilities_id'] === 10) {
                $nextRow = $row;
                ++$row;

                // A～D列を2行結合
                $activeSheet->mergeCells("A{$row}:A{$nextRow}");
                $activeSheet->mergeCells("B{$row}:B{$nextRow}");
                $activeSheet->mergeCells("C{$row}:C{$nextRow}");
                $activeSheet->mergeCells("D{$row}:D{$nextRow}");

                $activeSheet->setCellValue("E{$row}", '原料売上高'); // 貸方科目名
                $activeSheet->setCellValue("F{$row}", '4106');       // 貸方科目
                $activeSheet->setCellValue("H{$row}", 0);
                $activeSheet->setCellValue("I{$row}", 0);
                $activeSheet->setCellValue("J{$row}", 0);
                $activeSheet->setCellValue("K{$row}", 0);

                // スタイルの設定
                $activeSheet->getStyle("A{$row}:L{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $activeSheet->getStyle("H{$row}:K{$row}")->getNumberFormat()->setFormatCode('#,##0');
            }

            ++$row;
        }

        // 行番号をsetToatalに渡すためメンバ変数で保持
        $this->last_row_detail = $row;
    }

    /**
     * 合計行の出力
     *
     * @param Worksheet $activeSheet
     * @param array $outputData
     * @return void
     */
    private function setTotal(Worksheet $activeSheet, array $outputData): void
    {
        // 合計行の行番号
        $row = $this->last_row_detail;

        // 合計ラベルの設定
        $activeSheet->setCellValue("A{$row}", '合計');

        // 各列の合計を計算
        $totalSubTotal = 0;
        $totalRegularTaxSubTotal = 0;
        $totalReducedTaxSubTotal = 0;
        $totalFreeTaxSubTotal = 0;

        foreach ($outputData as $data) {
            $totalSubTotal += $data['total_sub_total'];
            $totalReducedTaxSubTotal += $data['total_reduced_tax_sub_total'];
            $totalRegularTaxSubTotal += $data['total_regular_tax_sub_total'];
            $totalFreeTaxSubTotal += $data['total_free_tax_sub_total'];
        }

        // 金額合計をセット
        $activeSheet->setCellValue("H{$row}", $totalSubTotal);
        $activeSheet->setCellValue("I{$row}", $totalReducedTaxSubTotal);
        $activeSheet->setCellValue("J{$row}", $totalRegularTaxSubTotal);
        $activeSheet->setCellValue("K{$row}", $totalFreeTaxSubTotal);

        // スタイルの設定
        $activeSheet->getStyle("A{$row}:L{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $activeSheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $activeSheet->getStyle("A{$row}:L{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_DOUBLE);

        // 数値セルの書式設定
        $activeSheet->getStyle("H{$row}:K{$row}")->getNumberFormat()->setFormatCode('#,##0');
    }

    /**
     * Excelシートの設定
     *
     * @param Worksheet $activeSheet
     * @return void
     */
    private function setPageSetup(Worksheet $activeSheet): void
    {
        $activeSheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $activeSheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $activeSheet->getPageSetup()->setFitToWidth(1);
        $activeSheet->getPageSetup()->setFitToHeight(0);
    }

    /**
     * 帳票出力データを取得
     *
     * @param array $searchConditions
     * @return array
     */
    public function getOutputData(array $searchConditions): array
    {
        // 出力データの取得
        $result = SalesOrderDetail::query()
            ->select([
                'm_office_facilities.id AS office_facilities_id',
                'm_office_facilities.name AS office_facilities_name',
                'm_accounting_codes.code AS accounting_code_code',
                'm_accounting_codes.name AS accounting_code_name',
                DB::raw('SUM(sales_order_details.sub_total) as total_sub_total'),
                DB::raw('SUM(CASE WHEN sales_order_details.reduced_tax_flag = 0
                                          AND sales_order_details.tax_type_id != ' . TaxType::TAX_EXEMPT . '
                                         THEN sales_order_details.sub_total ELSE 0 END) AS total_regular_tax_sub_total'),
                DB::raw('SUM(CASE WHEN sales_order_details.reduced_tax_flag = 1
                                         THEN sales_order_details.sub_total ELSE 0 END) AS total_reduced_tax_sub_total'),
                DB::raw('SUM(CASE WHEN sales_order_details.reduced_tax_flag = 0
                                          AND sales_order_details.tax_type_id = ' . TaxType::TAX_EXEMPT . '
                                         THEN sales_order_details.sub_total ELSE 0 END) AS total_free_tax_sub_total'),
            ])
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_details.sales_order_id')
            ->join('m_office_facilities', 'm_office_facilities.id', '=', 'sales_orders.office_facilities_id')
            ->join('m_products', 'm_products.id', '=', 'sales_order_details.product_id')
            ->join('m_accounting_codes', 'm_accounting_codes.id', '=', 'm_products.accounting_code_id')
            // 伝票日付で絞り込み
            ->when($searchConditions !== null, function ($query) use ($searchConditions) {
                $start_date = $searchConditions['order_date']['start'] ?? null;
                $end_date = $searchConditions['order_date']['end'] ?? null;

                if (is_null($start_date) && is_null($end_date)) {
                    return $query;
                }
                if (isset($start_date) && is_null($end_date)) {
                    return $query->where('sales_orders.order_date', '>=', $start_date);
                }
                if (is_null($start_date) && isset($end_date)) {
                    return $query->where('sales_orders.order_date', '<=', $end_date);
                }

                return $query->whereBetween('sales_orders.order_date', [$start_date, $end_date]);
            })
            ->where('sales_orders.transaction_type_id', MasterTransactionType::getCreditSalesId())
            ->where('sales_orders.department_id', MasterDepartment::getRetailId())
            ->where('m_accounting_codes.id', MasterAccountingCode::getAccountsReceivableId())
            ->groupBy(
                'm_office_facilities.id', 'm_office_facilities.name',
                'm_accounting_codes.id', 'm_accounting_codes.code', 'm_accounting_codes.name'
            )
            ->orderBy('m_office_facilities.id')
            ->get()
            ->toArray();

        //        dd($result);
        return $result;
    }
}
