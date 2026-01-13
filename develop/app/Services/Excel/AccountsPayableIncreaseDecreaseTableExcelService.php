<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterSupplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountsPayableIncreaseDecreaseTableExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 4;

    /** 明細行の終わりの行 */
    protected int $last_row_detail;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.accounts_payable_increase_decrease_table')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.accounts_payable_increase_decrease_table')
            . $prefix . config('consts.pdf.filename.file_extension');

        parent::__construct($downloadExcelFileName, $downloadPdfFileName);
    }

    /**
     * Excelデータ作成
     *
     * @param array $searchConditions
     * @param array $outputData
     * @param ?Carbon $baseMonth
     * @param bool $isPdf
     * @return Spreadsheet
     *
     * @throws Exception
     */
    public function getSpreadSheet(array $searchConditions, array $outputData, ?Carbon $baseMonth = null, bool $isPdf = false): Spreadsheet
    {
        if ($baseMonth === null) {
            $baseMonth = Carbon::parse($searchConditions['year_month'] . '-01');
        }

        // テンプレートファイルの読み込み
        $activeSheet = $this->initSpreadSheet(storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.accounts_payable_increase_decrease_table')
        ), PageSetup::ORIENTATION_LANDSCAPE);

        // ヘッダー行の出力
        $this->setHeader($activeSheet, $searchConditions);

        // タイトル行の出力
        $this->setTitle($activeSheet, $baseMonth);

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
        // 事業所名の出力
        if (isset($searchConditions['office_facility_id']) && $searchConditions['office_facility_id']) {
            $officeFacility = MasterOfficeFacility::find($searchConditions['office_facility_id']);
            $activeSheet->setCellValue('B1', $officeFacility->name);
        }

        $activeSheet->setCellValue('E1', '＊＊ 買掛残高一覧 ＊＊ ');

        // 期間の設定
        if (isset($searchConditions['year_month']) && $searchConditions['year_month']) {
            $baseMonth = Carbon::parse($searchConditions['year_month'] . '-01');
            $startMonth = $baseMonth->copy()->subMonths(3);
            $endMonth = $baseMonth;
            $activeSheet->setCellValue('I1', $startMonth->format('Y年m月') . '～' . $endMonth->format('Y年m月'));
        }
    }

    /**
     * タイトル行の出力
     *
     * @param Worksheet $activeSheet
     * @param Carbon $baseMonth
     * @return void
     */
    private function setTitle(Worksheet $activeSheet, Carbon $baseMonth): void
    {
        // カラム幅の設定
        $columnWidths = [
            'A' => 40, 'B' => 12, 'C' => 10, 'D' => 10, 'E' => 10,
            'F' => 10, 'G' => 10, 'H' => 10, 'I' => 10, 'J' => 10,
            'K' => 10, 'L' => 10, 'M' => 10, 'N' => 10,
        ];

        foreach ($columnWidths as $column => $width) {
            $activeSheet->getColumnDimension($column)->setWidth($width);
        }

        // セルの結合
        $activeSheet->mergeCells('B2:E2');
        $activeSheet->mergeCells('F2:H2');
        $activeSheet->mergeCells('I2:K2');
        $activeSheet->mergeCells('L2:N2');

        // 各月行の設定（2行目）
        $month3 = $baseMonth->copy()->subMonths(3)->format('n月');
        $month2 = $baseMonth->copy()->subMonths(2)->format('n月');
        $month1 = $baseMonth->copy()->subMonths(1)->format('n月');
        $month0 = $baseMonth->format('n月');

        $activeSheet->setCellValue('B2', $month3);
        $activeSheet->setCellValue('F2', $month2);
        $activeSheet->setCellValue('I2', $month1);
        $activeSheet->setCellValue('L2', $month0);

        $activeSheet->getStyle('B2:M2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // タイトル行の設定（3行目）
        $activeSheet->setCellValue('A3', '仕入先名');
        $activeSheet->setCellValue('B3', '繰越額');

        // 月次データ（「仕入」「支払」「残高」の繰り返し）
        $monthlyLabels = ['仕　入', '支　払', '残　高'];

        // C3からN3まで4ヶ月分
        $startColumn = 'C';
        $months = 4;

        for ($month = 0; $month < $months; ++$month) {
            foreach ($monthlyLabels as $index => $label) {
                // 列の計算（C, D, E, F, ...）
                $column = chr(ord($startColumn) + $month * count($monthlyLabels) + $index);
                $cell = $column . '3';
                $activeSheet->setCellValue($cell, $label);
                // スタイルの設定
                $activeSheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        // スタイルの設定
        $activeSheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $activeSheet->getStyle('A2:N3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    /**
     * 明細行の出力
     *
     * @param Worksheet $activeSheet
     * @param array $outputData DBから取得した明細データ
     * @return void
     */
    private function setDetails(Worksheet $activeSheet, array $outputData): void
    {
        $row = $this->start_row_detail;
        $start_row = $row;

        foreach ($outputData as $data) {
            // 各月の残高計算
            $carryover = $data['carryover'];
            $balance3 = $carryover + $data['total_purchase_3_months_ago'] - $data['total_payment_3_months_ago'];
            $balance2 = $balance3 + $data['total_purchase_2_months_ago'] - $data['total_payment_2_months_ago'];
            $balance1 = $balance2 + $data['total_purchase_1_month_ago'] - $data['total_payment_1_month_ago'];
            $balance0 = $balance1 + $data['total_purchase_current_month'] - $data['total_payment_current_month'];

            // 仕入先名
            $activeSheet->setCellValue("A{$row}", $data['name']);

            // 繰越額
            $activeSheet->setCellValue("B{$row}", $carryover);

            // 3ヶ月前のデータ
            $activeSheet->setCellValue("C{$row}", $data['total_purchase_3_months_ago']);
            $activeSheet->setCellValue("D{$row}", $data['total_payment_3_months_ago']);
            $activeSheet->setCellValue("E{$row}", $balance3);

            // 2ヶ月前のデータ
            $activeSheet->setCellValue("F{$row}", $data['total_purchase_2_months_ago']);
            $activeSheet->setCellValue("G{$row}", $data['total_payment_2_months_ago']);
            $activeSheet->setCellValue("H{$row}", $balance2);

            // 1ヶ月前のデータ
            $activeSheet->setCellValue("I{$row}", $data['total_purchase_1_month_ago']);
            $activeSheet->setCellValue("J{$row}", $data['total_payment_1_month_ago']);
            $activeSheet->setCellValue("K{$row}", $balance1);

            // 当月のデータ
            $activeSheet->setCellValue("L{$row}", $data['total_purchase_current_month']);
            $activeSheet->setCellValue("M{$row}", $data['total_payment_current_month']);
            $activeSheet->setCellValue("N{$row}", $balance0);

            ++$row;
        }

        // 行番号をsetToatalに渡すためメンバ変数で保持
        $this->last_row_detail = $row;

        // スタイルの設定
        $activeSheet->getStyle("A{$start_row}:N{$this->last_row_detail}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $activeSheet->getStyle("B{$start_row}:N{$this->last_row_detail}")->getNumberFormat()->setFormatCode('#,##0');
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
        $activeSheet->setCellValue("A{$row}", '☆　部門計　☆');

        // 各列の合計を計算
        $totalCarryover = 0;
        $totalPurchase3MonthsAgo = 0;
        $totalPayment3MonthsAgo = 0;
        $totalBalance3 = 0;
        $totalPurchase2MonthsAgo = 0;
        $totalPayment2MonthsAgo = 0;
        $totalBalance2 = 0;
        $totalPurchase1MonthAgo = 0;
        $totalPayment1MonthAgo = 0;
        $totalBalance1 = 0;
        $totalPurchaseCurrentMonth = 0;
        $totalPaymentCurrentMonth = 0;
        $totalBalance0 = 0;

        foreach ($outputData as $data) {
            $carryover = $data['carryover'];
            $balance3 = $carryover + $data['total_purchase_3_months_ago'] - $data['total_payment_3_months_ago'];
            $balance2 = $balance3 + $data['total_purchase_2_months_ago'] - $data['total_payment_2_months_ago'];
            $balance1 = $balance2 + $data['total_purchase_1_month_ago'] - $data['total_payment_1_month_ago'];
            $balance0 = $balance1 + $data['total_purchase_current_month'] - $data['total_payment_current_month'];

            $totalCarryover += $carryover;
            $totalPurchase3MonthsAgo += $data['total_purchase_3_months_ago'];
            $totalPayment3MonthsAgo += $data['total_payment_3_months_ago'];
            $totalBalance3 += $balance3;
            $totalPurchase2MonthsAgo += $data['total_purchase_2_months_ago'];
            $totalPayment2MonthsAgo += $data['total_payment_2_months_ago'];
            $totalBalance2 += $balance2;
            $totalPurchase1MonthAgo += $data['total_purchase_1_month_ago'];
            $totalPayment1MonthAgo += $data['total_payment_1_month_ago'];
            $totalBalance1 += $balance1;
            $totalPurchaseCurrentMonth += $data['total_purchase_current_month'];
            $totalPaymentCurrentMonth += $data['total_payment_current_month'];
            $totalBalance0 += $balance0;
        }

        // 合計値をセルに設定
        $activeSheet->setCellValue("B{$row}", $totalCarryover);
        $activeSheet->setCellValue("C{$row}", $totalPurchase3MonthsAgo);
        $activeSheet->setCellValue("D{$row}", $totalPayment3MonthsAgo);
        $activeSheet->setCellValue("E{$row}", $totalBalance3);
        $activeSheet->setCellValue("F{$row}", $totalPurchase2MonthsAgo);
        $activeSheet->setCellValue("G{$row}", $totalPayment2MonthsAgo);
        $activeSheet->setCellValue("H{$row}", $totalBalance2);
        $activeSheet->setCellValue("I{$row}", $totalPurchase1MonthAgo);
        $activeSheet->setCellValue("J{$row}", $totalPayment1MonthAgo);
        $activeSheet->setCellValue("K{$row}", $totalBalance1);
        $activeSheet->setCellValue("L{$row}", $totalPurchaseCurrentMonth);
        $activeSheet->setCellValue("M{$row}", $totalPaymentCurrentMonth);
        $activeSheet->setCellValue("N{$row}", $totalBalance0);

        // スタイルの設定
        $activeSheet->getStyle("A{$row}:N{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $activeSheet->getStyle("B{$row}:N{$row}")->getNumberFormat()->setFormatCode('#,##0');
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

        $baseMonth = Carbon::parse($searchConditions['year_month'] . '-01');
        $three_months_ago_start = $baseMonth->copy()->subMonths(3)->startOfMonth();
        $three_months_ago_end = $baseMonth->copy()->subMonths(3)->endOfMonth();
        $two_months_ago_start = $baseMonth->copy()->subMonths(2)->startOfMonth();
        $two_months_ago_end = $baseMonth->copy()->subMonths(2)->endOfMonth();
        $one_month_ago_start = $baseMonth->copy()->subMonth()->startOfMonth();
        $one_month_ago_end = $baseMonth->copy()->subMonth()->endOfMonth();
        $current_month_start = $baseMonth->copy()->startOfMonth();
        $current_month_end = $baseMonth->copy()->endOfMonth();
        $closing_month = $baseMonth->copy()->subMonths(4)->format('Ym');

        // サブクエリで各月の仕入データを取得
        $purchaseQuery = DB::table('purchase_orders')
            ->select(
                'supplier_id',
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$three_months_ago_start}' AND '{$three_months_ago_end}' THEN purchase_total ELSE 0 END) as total_purchase_3_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$two_months_ago_start}' AND '{$two_months_ago_end}' THEN purchase_total ELSE 0 END) as total_purchase_2_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$one_month_ago_start}' AND '{$one_month_ago_end}' THEN purchase_total ELSE 0 END) as total_purchase_1_month_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$current_month_start}' AND '{$current_month_end}' THEN purchase_total ELSE 0 END) as total_purchase_current_month")
            )
            ->where('department_id', '=', $department_id)
            ->where('office_facilities_id', '=', $office_facility_id)
            ->whereNull('deleted_at')
            ->groupBy('supplier_id');

        // サブクエリで各月の支払データを取得
        $paymentQuery = DB::table('payments')
            ->select(
                'supplier_id',
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$three_months_ago_start}' AND '{$three_months_ago_end}' THEN payment ELSE 0 END) as total_payment_3_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$two_months_ago_start}' AND '{$two_months_ago_end}' THEN payment ELSE 0 END) as total_payment_2_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$one_month_ago_start}' AND '{$one_month_ago_end}' THEN payment ELSE 0 END) as total_payment_1_month_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$current_month_start}' AND '{$current_month_end}' THEN payment ELSE 0 END) as total_payment_current_month")
            )
            ->where('department_id', '=', $department_id)
            ->where('office_facilities_id', '=', $office_facility_id)
            ->whereNull('deleted_at')
            ->groupBy('supplier_id');

        // 出力データの取得
        return MasterSupplier::query()
            ->select(
                'm_suppliers.code',
                'm_suppliers.name',
                DB::raw('COALESCE(pc.purchase_closing_total, 0) as carryover'),
                DB::raw('COALESCE(p.total_purchase_3_months_ago, 0) as total_purchase_3_months_ago'),
                DB::raw('COALESCE(p.total_purchase_2_months_ago, 0) as total_purchase_2_months_ago'),
                DB::raw('COALESCE(p.total_purchase_1_month_ago, 0) as total_purchase_1_month_ago'),
                DB::raw('COALESCE(p.total_purchase_current_month, 0) as total_purchase_current_month'),
                DB::raw('COALESCE(pay.total_payment_3_months_ago, 0) as total_payment_3_months_ago'),
                DB::raw('COALESCE(pay.total_payment_2_months_ago, 0) as total_payment_2_months_ago'),
                DB::raw('COALESCE(pay.total_payment_1_month_ago, 0) as total_payment_1_month_ago'),
                DB::raw('COALESCE(pay.total_payment_current_month, 0) as total_payment_current_month')
            )
            ->leftJoin('purchase_closing AS pc', function ($join) use ($closing_month, $department_id, $office_facility_id) {
                $join->on('pc.supplier_id', '=', 'm_suppliers.id')
                    ->where('pc.closing_ym', '=', $closing_month)
                    ->where('pc.department_id', '=', $department_id)
                    ->where('pc.office_facilities_id', '=', $office_facility_id)
                    ->whereNull('pc.deleted_at');
            })
            ->leftJoinSub($purchaseQuery, 'p', function ($join) {
                $join->on('p.supplier_id', '=', 'm_suppliers.id');
            })
            ->leftJoinSub($paymentQuery, 'pay', function ($join) {
                $join->on('pay.supplier_id', '=', 'm_suppliers.id');
            })
            // WHEREを使って、繰越額、購入額、支払額がゼロでないものだけを取得
            ->whereRaw('
                COALESCE(pc.purchase_closing_total, 0) != 0 OR
                COALESCE(p.total_purchase_3_months_ago, 0) != 0 OR
                COALESCE(p.total_purchase_2_months_ago, 0) != 0 OR
                COALESCE(p.total_purchase_1_month_ago, 0) != 0 OR
                COALESCE(p.total_purchase_current_month, 0) != 0 OR
                COALESCE(pay.total_payment_3_months_ago, 0) != 0 OR
                COALESCE(pay.total_payment_2_months_ago, 0) != 0 OR
                COALESCE(pay.total_payment_1_month_ago, 0) != 0 OR
                COALESCE(pay.total_payment_current_month, 0) != 0
            ')
            ->orderBy('m_suppliers.code')
            ->get()
            ->toArray();
    }
}
