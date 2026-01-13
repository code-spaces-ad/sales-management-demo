<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Enums\PurchaseClassification;
use App\Enums\SalesClassification;
use App\Helpers\DateHelper;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlySalesBookExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 3;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.monthly_sales_book')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.monthly_sales_book')
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
            . config('consts.excel.template_file.monthly_sales_book')
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
     * @param Worksheet $activeSheet
     * @param array $searchConditions
     * @return void
     */
    private function setHeader(Worksheet $activeSheet, array $searchConditions): void
    {
        // 期間の設定
        if (isset($searchConditions['year_month']) && $searchConditions['year_month']) {
            $yearMonth = Carbon::createFromFormat('Y-m', $searchConditions['year_month']);
            $activeSheet->setCellValue('A1', $yearMonth->format('Y年m月度'));
        }

        // 事業所名の出力
        if (isset($searchConditions['office_facility_id']) && $searchConditions['office_facility_id']) {
            $officeFacility = MasterOfficeFacility::find($searchConditions['office_facility_id']);
            $activeSheet->setCellValue('C1', $officeFacility->name);
        }

        $activeSheet->setCellValue('G1', '＊＊ 月 間 商 品 売 上 簿 ＊＊ ');
        $activeSheet->setCellValue('K1', 'DATE　:　' . Carbon::now()->format('Y/m/d'));

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
            'A' => 12, 'B' => 38, 'C' => 9, 'D' => 9, 'E' => 9, 'F' => 9,
            'G' => 9, 'H' => 9, 'I' => 9, 'J' => 9, 'K' => 9, 'L' => 9,
            'M' => 9, 'N' => 9, 'O' => 9, 'P' => 15, 'Q' => 9, 'R' => 9,
        ];

        foreach ($columnWidths as $column => $width) {
            $activeSheet->getColumnDimension($column)->setWidth($width);
        }

        // タイトル行の設定（2行目）
        $activeSheet->setCellValue('A2', '商品CD');
        $activeSheet->setCellValue('B2', '商　品　名');
        $activeSheet->setCellValue('C2', '原　価');
        $activeSheet->setCellValue('D2', '前月残');
        $activeSheet->setCellValue('E2', '仕　入');
        $activeSheet->setCellValue('F2', '返　品');
        $activeSheet->setCellValue('G2', '入　庫');
        $activeSheet->setCellValue('H2', '売　上');
        $activeSheet->setCellValue('I2', '返　品');
        $activeSheet->setCellValue('J2', '出　庫');
        $activeSheet->setCellValue('K2', 'ｻｰﾋﾞｽ');
        $activeSheet->setCellValue('L2', '試　飲');
        $activeSheet->setCellValue('M2', 'その他');
        $activeSheet->setCellValue('N2', '調　整');
        $activeSheet->setCellValue('O2', '今月残');
        $activeSheet->setCellValue('P2', '在庫金額');
        $activeSheet->setCellValue('Q2', '棚卸数');
        $activeSheet->setCellValue('R2', '差　数');

        // スタイルの設定
        $activeSheet->getStyle('A2:R2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $activeSheet->getStyle('A2:R2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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

        foreach ($outputData as $data) {
            $activeSheet->setCellValue("A{$row}", $data['code'] ?? '');
            $activeSheet->setCellValue("B{$row}", $data['name'] ?? '');
            $activeSheet->setCellValue("C{$row}", $data['purchase_unit_price'] ?? '');
            $activeSheet->setCellValue("D{$row}", $data['prev_closing_stocks'] ?? '');
            $activeSheet->setCellValue("E{$row}", ($data['purchase_pur_quantity'] ?? 0) != 0 ? $data['purchase_pur_quantity'] : '');
            $activeSheet->setCellValue("F{$row}", '');
            $activeSheet->setCellValue("G{$row}", ($data['stock_quantity'] ?? 0) != 0 ? $data['stock_quantity'] : '');
            $activeSheet->setCellValue("H{$row}", ($data['sales_pur_quantity'] ?? 0) != 0 ? $data['sales_pur_quantity'] : '');
            $activeSheet->setCellValue("I{$row}", ($data['sales_rtn_quantity'] ?? 0) != 0 ? $data['sales_rtn_quantity'] : '');
            $activeSheet->setCellValue("J{$row}", ($data['issue_quantity'] ?? 0) != 0 ? $data['issue_quantity'] : '');
            $activeSheet->setCellValue("K{$row}", ($data['sales_sev_quantity'] ?? 0) != 0 ? $data['sales_sev_quantity'] : '');
            $activeSheet->setCellValue("L{$row}", ($data['sales_tas_quantity'] ?? 0) != 0 ? $data['sales_tas_quantity'] : '');
            $activeSheet->setCellValue("M{$row}", ($data['sales_oth_quantity'] ?? 0) != 0 ? $data['sales_oth_quantity'] : '');
            $activeSheet->setCellValue("N{$row}", '');
            $activeSheet->setCellValue("O{$row}", $data['now_closing_stocks'] ?? '');
            $activeSheet->setCellValue("P{$row}", $data['purchase_unit_price'] * $data['now_closing_stocks'] ?? 0);
            $activeSheet->setCellValue("Q{$row}", $data['prev_closing_stocks'] ?? '');
            $activeSheet->setCellValue("R{$row}", $data['prev_closing_stocks'] - $data['now_closing_stocks'] ?? '');

            // スタイルの設定
            $activeSheet->getStyle("A{$row}:R{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $activeSheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode('#,##0');

            ++$row;
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
        $activeSheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $activeSheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $activeSheet->getPageSetup()->setFitToWidth(1);
        $activeSheet->getPageSetup()->setFitToHeight(0);
        $activeSheet->setSelectedCell('A1');
        $activeSheet->getHeaderFooter()->setOddHeader('&RPAGE　:　&P'); // 奇数ページ用
        $activeSheet->getHeaderFooter()->setEvenHeader('&RPAGE　:　&P'); // 偶数ページ用
    }

    /**
     * 帳票出力データを取得
     *
     * @param array $searchConditions
     * @return array
     */
    public function getOutputData(array $searchConditions): array
    {
        $office_facility_id = $searchConditions['office_facility_id'] ?? null;
        $start_date = DateHelper::getMonthStart($searchConditions['year_month'] ?? null);
        $end_date = DateHelper::getMonthEnd($searchConditions['year_month'] ?? null);

        $year_month = str_replace('-', '', $searchConditions['year_month'] ?? null);
        $date = Carbon::createFromFormat('Ym', $year_month);
        $prevMonth = $date->subMonth()->format('Ym');

        // サブクエリで仕入データを取得
        $purchaseSub = DB::table('purchase_order_details AS pod')
            ->join('purchase_orders AS po', 'pod.purchase_order_id', '=', 'po.id')
            ->select(
                'pod.product_id',
                DB::raw('CAST(SUM(CASE WHEN po.purchase_classification_id = ' . PurchaseClassification::CLASSIFICATION_PURCHASE . ' THEN pod.quantity ELSE 0 END) AS SIGNED) AS purchase_pur_quantity'),
            )
            ->whereBetween('po.order_date', [$start_date, $end_date])
            ->where('po.office_facilities_id', $office_facility_id)
            ->whereNull('po.deleted_at')
            ->whereNull('pod.deleted_at')
            ->groupBy('pod.product_id');

        // サブクエリで売上データを取得
        $salesSub = DB::table('sales_order_details AS sod')
            ->join('sales_orders AS so', 'sod.sales_order_id', '=', 'so.id')
            ->select(
                'sod.product_id',
                DB::raw('CAST(SUM(CASE WHEN so.sales_classification_id = ' . SalesClassification::CLASSIFICATION_SALE . ' THEN sod.quantity ELSE 0 END) AS SIGNED) AS sales_pur_quantity'),
                DB::raw('CAST(SUM(CASE WHEN so.sales_classification_id = ' . SalesClassification::CLASSIFICATION_RETURN . ' THEN sod.quantity ELSE 0 END) AS SIGNED) AS sales_rtn_quantity'),
                DB::raw('CAST(SUM(CASE WHEN so.sales_classification_id = ' . SalesClassification::CLASSIFICATION_SERVICE . ' THEN sod.quantity ELSE 0 END) AS SIGNED) AS sales_sev_quantity'),
                DB::raw('CAST(SUM(CASE WHEN so.sales_classification_id = ' . SalesClassification::CLASSIFICATION_TASTING . ' THEN sod.quantity ELSE 0 END) AS SIGNED) AS sales_tas_quantity'),
                DB::raw('CAST(SUM(CASE WHEN so.sales_classification_id = ' . SalesClassification::CLASSIFICATION_OTHER . ' THEN sod.quantity ELSE 0 END) AS SIGNED) AS sales_oth_quantity')
            )
            ->whereBetween('so.order_date', [$start_date, $end_date])
            ->where('so.office_facilities_id', $office_facility_id)
            ->whereNull('so.deleted_at')
            ->whereNull('sod.deleted_at')
            ->groupBy('sod.product_id');

        // サブクエリで入出庫データを取得
        $inventorySub = DB::table('inventory_data_details AS idd')
            ->join('inventory_datas AS ids', 'idd.inventory_data_id', '=', 'ids.id')
            ->select(
                'idd.product_id',
                DB::raw('CAST(SUM(CASE WHEN ids.to_warehouse_id = ' . $office_facility_id . ' THEN idd.quantity ELSE 0 END) AS SIGNED) AS stock_quantity'),
                DB::raw('CAST(SUM(CASE WHEN ids.from_warehouse_id = ' . $office_facility_id . ' THEN idd.quantity ELSE 0 END) AS SIGNED) AS issue_quantity')
            )
            ->whereBetween('ids.inout_date', [$start_date, $end_date])
            ->where(function ($q) use ($office_facility_id) {
                $q->where('ids.from_warehouse_id', '=', $office_facility_id)
                    ->orWhere('ids.to_warehouse_id', '=', $office_facility_id);
            })
            ->groupBy('idd.product_id');

        // サブクエリで締在庫数を取得
        $prevClosingSub = DB::table('inventory_data_closing AS prev')
            ->select('prev.product_id', 'prev.closing_stocks AS prev_closing_stocks')
            ->where('prev.closing_ym', '=', $prevMonth)
            ->where('prev.warehouse_id', '=', $office_facility_id);

        // サブクエリで現在庫数を取得
        $nowClosingSub = DB::table('inventory_data_closing AS now')
            ->select('now.product_id', 'now.closing_stocks AS now_closing_stocks')
            ->where('now.closing_ym', '=', $year_month)
            ->where('now.warehouse_id', '=', $office_facility_id);

        // 出力データの取得
        return MasterProduct::query()
            ->select(
                'm_products.code',
                'm_products.name',
                DB::raw('ROUND(m_products.purchase_unit_price, 0) AS purchase_unit_price'),
                'purchase.purchase_pur_quantity',
                'sales.sales_sev_quantity',
                'sales.sales_tas_quantity',
                'sales.sales_oth_quantity',
                'sales.sales_pur_quantity',
                'sales.sales_rtn_quantity',
                'inventory.stock_quantity',
                'inventory.issue_quantity',
                'prev.prev_closing_stocks',
                'now.now_closing_stocks'
            )
            ->leftJoinSub($purchaseSub, 'purchase', 'm_products.id', '=', 'purchase.product_id')
            ->leftJoinSub($salesSub, 'sales', 'm_products.id', '=', 'sales.product_id')
            ->leftJoinSub($inventorySub, 'inventory', 'm_products.id', '=', 'inventory.product_id')
            ->leftJoinSub($prevClosingSub, 'prev', 'm_products.id', '=', 'prev.product_id')
            ->leftJoinSub($nowClosingSub, 'now', 'm_products.id', '=', 'now.product_id')

            // どれか1つでも0でない値が含まれていれば取得
            ->havingRaw('
                COALESCE(purchase.purchase_pur_quantity, 0) != 0 OR
                COALESCE(sales.sales_sev_quantity, 0) != 0 OR
                COALESCE(sales.sales_tas_quantity, 0) != 0 OR
                COALESCE(sales.sales_oth_quantity, 0) != 0 OR
                COALESCE(sales.sales_pur_quantity, 0) != 0 OR
                COALESCE(sales.sales_rtn_quantity, 0) != 0 OR
                COALESCE(inventory.stock_quantity, 0) != 0 OR
                COALESCE(inventory.issue_quantity, 0) != 0 OR
                COALESCE(prev.prev_closing_stocks, 0) != 0 OR
                COALESCE(now.now_closing_stocks, 0) != 0
            ')

            ->get()
            ->toArray();
    }
}
