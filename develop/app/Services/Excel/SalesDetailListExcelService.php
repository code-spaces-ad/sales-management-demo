<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Enums\TransactionType;
use App\Models\Sale\SalesOrder;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class SalesDetailListExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 8;

    /** 明細行の最大行 */
    protected int $max_row_detail = 18;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.sales_detail_list')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.sales_detail_list')
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
            . config('consts.excel.template_file.sales_detail_list')
        ), PageSetup::ORIENTATION_LANDSCAPE);
        $startRow = 2;

        // --- 検索条件から売上日の期間を取得 ---
        $start = $searchConditions['sales_date']['start'] ?? '';
        $end = $searchConditions['sales_date']['end'] ?? '';

        // --- 表示用に日付整形 ---
        $startDateStr = $start ? new Carbon($start)->format('Y/m/d') : '';
        $endDateStr = $end ? new Carbon($end)->format('Y/m/d') : '';
        $todayStr = now()->format('Y/m/d');

        // --- A1 に表示させる文字列を作成 ---
        $headerText = "売上日[{$startDateStr}]-[{$endDateStr}] ＊＊ 売上明細一覧(売上日指定) ＊＊ DATE : {$todayStr}";
        $activeSheet->setCellValue('A1', $headerText);

        // A列幅の調整
        $activeSheet->getColumnDimension('A')->setWidth(12);

        // A2～Q2 の罫線
        $activeSheet->getStyle('A2:Q2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // 列幅自動調整
        foreach (range('B', 'Q') as $columnID) {
            $activeSheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // --- A2～Q2 に表示させる文字列を作成 ---
        $activeSheet->setCellValue('A2', '売上日');
        $activeSheet->setCellValue('B2', '伝票No');
        $activeSheet->setCellValue('C2', '行No');
        $activeSheet->setCellValue('D2', '伝区');
        $activeSheet->setCellValue('E2', '得意先CD');
        $activeSheet->setCellValue('F2', '得意先名');
        $activeSheet->setCellValue('G2', '商品CD');
        $activeSheet->setCellValue('H2', '商品名');
        $activeSheet->setCellValue('I2', '倉庫CD');
        $activeSheet->setCellValue('J2', '倉庫名');
        $activeSheet->setCellValue('K2', '売区');
        $activeSheet->setCellValue('L2', '入数');
        $activeSheet->setCellValue('M2', '箱数');
        $activeSheet->setCellValue('N2', '数量');
        $activeSheet->setCellValue('O2', '単価');
        $activeSheet->setCellValue('P2', '金額');
        $activeSheet->setCellValue('Q2', '粗利金額');

        // データ行
        $currentRow = $startRow + 1;
        $lineNumber = 1;
        foreach ($outputData as $row) {
            $activeSheet->setCellValue("A{$currentRow}", $row['order_date']);
            $activeSheet->setCellValue("B{$currentRow}", $row['order_number']);
            $activeSheet->setCellValue("C{$currentRow}", $lineNumber);
            $activeSheet->setCellValue("D{$currentRow}", $row['transaction_type_id']
                . ' ' . TransactionType::getDescription($row['transaction_type_id']));
            $activeSheet->setCellValue("E{$currentRow}", $row['customer_code']);
            $activeSheet->setCellValue("F{$currentRow}", $row['customerName']);
            $activeSheet->setCellValue("G{$currentRow}", $row['products_code']);
            $activeSheet->setCellValue("H{$currentRow}", $row['products_name']);
            $activeSheet->setCellValue("I{$currentRow}", $row['warehouses_code']);
            $activeSheet->setCellValue("J{$currentRow}", $row['warehouses_name']);
            $activeSheet->setCellValue("K{$currentRow}", $row['sales_classification_id']);
            $activeSheet->setCellValue("N{$currentRow}", $row['quantity']);
            $activeSheet->setCellValue("O{$currentRow}", $row['unit_price']);
            $activeSheet->setCellValue("P{$currentRow}", $row['sub_total']);
            $activeSheet->setCellValue("Q{$currentRow}", $row['gross_profit']);

            // 罫線
            $activeSheet->getStyle("A{$currentRow}:Q{$currentRow}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            ++$currentRow;
            ++$lineNumber;
        }

        return $this->spreadSheet;
    }

    /**
     * 帳票出力データを取得
     *
     * @param array $searchConditions
     * @return array
     */
    public function getOutputData(array $searchConditions): array
    {
        return SalesOrder::query()
            ->select([
                'sales_orders.order_date',
                'sales_orders.order_number',
                'sales_orders.sales_classification_id',
                'sales_orders.transaction_type_id',
                'm_customers.code AS customer_code',
                'm_customers.name AS customerName', // Laravelのアクセサ（getCustomerNameAttribute）とバッティングするため、AS名変更
                'm_products.code AS products_code',
                'm_products.name AS products_name',
                'm_warehouses.code AS warehouses_code',
                'm_warehouses.name AS warehouses_name',
                'sales_order_details.quantity',
                'sales_order_details.unit_price',
                'sales_order_details.sub_total',
                'sales_order_details.gross_profit',
            ])
            ->join('sales_order_details', 'sales_orders.id', '=', 'sales_order_details.sales_order_id')
            ->Join('m_customers', 'sales_orders.customer_id', '=', 'm_customers.id')
            ->join('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
            ->join('m_warehouses', 'sales_orders.office_facilities_id', '=', 'm_warehouses.id')

            // 伝票日付で絞り込み
            ->when($searchConditions !== null, function ($query) use ($searchConditions) {
                $start_date = $searchConditions['sales_date']['start'] ?? null;
                $end_date = $searchConditions['sales_date']['end'] ?? null;

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

            ->orderBy('sales_orders.order_date')
            ->orderBy('sales_orders.order_number')
            ->get()
            ->toArray();
    }
}
