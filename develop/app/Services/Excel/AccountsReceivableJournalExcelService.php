<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Enums\TaxType;
use App\Models\Master\MasterAccountingCode;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterTransactionType;
use App\Models\Sale\SalesOrderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountsReceivableJournalExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 3;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.accounts_receivable_journal')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.accounts_receivable_journal')
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
            . config('consts.excel.template_file.accounts_receivable_journal')
        ), PageSetup::ORIENTATION_LANDSCAPE);

        // ヘッダー行の出力
        $this->setHeader($activeSheet, $searchConditions);

        // タイトル行の出力
        $this->setTitle($activeSheet);

        // 明細行の出力
        $this->setDetails($activeSheet, $outputData);

        // Excelシートの設定
        $this->setPageSetup($activeSheet);

        return $this->spreadSheet;
    }

    /**
     * ヘッダー行の出力
     *
     * @param array $searchConditions
     * @param array $outputData
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

        $activeSheet->setCellValue('D1', '＊＊ 仕　訳　帳 ＊＊');

        // 事業所名の出力
        if (isset($searchConditions['office_facility_id']) && $searchConditions['office_facility_id']) {
            $officeFacility = MasterOfficeFacility::find($searchConditions['office_facility_id']);
            $activeSheet->setCellValue('G1', $officeFacility->name);
        }
    }

    /**
     * タイトル行の出力
     *
     * @param Worksheet $activeSheet
     * @return void
     */
    private function setTitle(Worksheet $activeSheet): void
    {
        // カラム幅の設定
        $columnWidths = [
            'A' => 11, 'B' => 10, 'C' => 9, 'D' => 9, 'E' => 10,
            'F' => 9, 'G' => 9, 'H' => 9, 'I' => 11, 'J' => 8, 'K' => 13,
        ];

        foreach ($columnWidths as $column => $width) {
            $activeSheet->getColumnDimension($column)->setWidth($width);
        }

        // タイトル行の設定
        $cellValues = [
            'A2' => '日　付',
            'B2' => '借方科目名',
            'C2' => '借方科目',
            'D2' => '借方補助',
            'E2' => '貸方科目名',
            'F2' => '貸方科目',
            'G2' => '貸方補助',
            'H2' => '消費税ｺｰﾄﾞ',
            'I2' => '金　額',
            'J2' => '摘要ｺｰﾄﾞ',
            'K2' => '摘　要',
        ];

        foreach ($cellValues as $cell => $value) {
            $activeSheet->setCellValue($cell, $value);
        }

        // スタイルの設定
        $styleRanges = [
            'A2:K2',
        ];

        foreach ($styleRanges as $range) {
            $style = $activeSheet->getStyle($range);
            $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
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
            // 明細行（1行目）
            // 日付
            $activeSheet->setCellValue("A{$row}", Carbon::parse($data['order_date'])->format('Y/m/d'));

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

            // 金額
            $activeSheet->setCellValue("I{$row}", $data['total_sub_total'] ?? 0);
            $activeSheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0');

            // 摘要
            $activeSheet->setCellValue("K{$row}", $data['memo'] ?? '');

            // 営業推進部の場合、2行目を追加
            if ($data['office_facilities_id'] === 10) {
                $nextRow = $row;
                ++$row;

                // A～D列を2行結合
                $activeSheet->mergeCells("A{$row}:A{$nextRow}");
                $activeSheet->mergeCells("B{$row}:B{$nextRow}");
                $activeSheet->mergeCells("C{$row}:C{$nextRow}");
                $activeSheet->mergeCells("D{$row}:D{$nextRow}");
                $activeSheet->mergeCells("G{$row}:G{$nextRow}");
                $activeSheet->mergeCells("H{$row}:H{$nextRow}");
                $activeSheet->mergeCells("J{$row}:J{$nextRow}");
                $activeSheet->mergeCells("K{$row}:K{$nextRow}");

                $activeSheet->setCellValue("E{$row}", '原料売上高'); // 貸方科目名
                $activeSheet->setCellValue("F{$row}", '4106');       // 貸方科目
                $activeSheet->setCellValue("I{$row}", 0);
            }

            ++$row;

            // 消費税8%（2行目）
            $activeSheet->setCellValue("H{$row}", '8%');
            $reduced_total = ($data['total_reduced_tax_sub_total'] ?? 0);
            $activeSheet->setCellValue("I{$row}", $reduced_total);
            $activeSheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $activeSheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            ++$row;

            // 消費税10%（3行目）
            $activeSheet->setCellValue("H{$row}", '10%');
            $normal_total = ($data['total_regular_tax_sub_total'] ?? 0);
            $activeSheet->setCellValue("I{$row}", $normal_total);
            $activeSheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $activeSheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            ++$row;

            // 非課税（4行目）　※データがあれば出力
            if (!empty($data['total_free_tax_sub_total'])) {
                $activeSheet->setCellValue("H{$row}", '非課税');
                $activeSheet->setCellValue("I{$row}", $data['total_free_tax_sub_total']);
                $activeSheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $activeSheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                ++$row;
            }
        }
    }

    /**
     * Excelシートの設定
     *
     * @param Worksheet $activeSheet
     * @return void
     */
    private function setPageSetup(Worksheet $activeSheet): void
    {
        $activeSheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $activeSheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $activeSheet->getPageSetup()->setFitToWidth(1);
        $activeSheet->getPageSetup()->setFitToHeight(0);
        $activeSheet->getStyle($activeSheet->calculateWorksheetDimension())->getFont()->setSize(10);
        $activeSheet->setSelectedCell('A1');
    }

    /**
     * 帳票出力データを取得
     *
     * @param array $searchConditions
     * @return array
     */
    public function getOutputData(array $searchConditions): array
    {
        $department_id = $searchConditions['department_id'] ?? null;
        $office_facility_id = $searchConditions['office_facility_id'] ?? null;

        // 出力データの取得
        return SalesOrderDetail::query()
            ->select([
                'sales_orders.order_date',
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
                'sales_orders.office_facilities_id',
                'sales_orders.memo',
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
            // 部門IDで絞り込み
            ->when($department_id !== null, function ($query) use ($department_id) {
                return $query->where('sales_orders.department_id', $department_id);
            })
            // 事業所IDで絞り込み
            ->when($office_facility_id !== null, function ($query) use ($office_facility_id) {
                return $query->where('sales_orders.office_facilities_id', $office_facility_id);
            })
            ->where('sales_orders.transaction_type_id', MasterTransactionType::getCreditSalesId())
            ->where('m_accounting_codes.id', MasterAccountingCode::getAccountsReceivableId())
            ->groupBy([
                'sales_orders.order_date',
                'sales_orders.office_facilities_id',
                'm_accounting_codes.name',
                'm_accounting_codes.code',
                'sales_orders.memo',
            ])
            ->orderBy('sales_orders.order_date')
            ->get()
            ->toArray();
    }
}
