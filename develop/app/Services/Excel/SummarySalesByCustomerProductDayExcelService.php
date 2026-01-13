<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Enums\SalesClassification;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterProduct;
use App\Models\Sale\SalesOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class SummarySalesByCustomerProductDayExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 8;

    /** 明細行の最大行 */
    protected int $max_row_detail = 18;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.summary_sales_by_customer_product_day')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.summary_sales_by_customer_product_day')
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
            . config('consts.excel.template_file.summary_sales_by_customer_product_day')
        ), PageSetup::ORIENTATION_LANDSCAPE);

        $start_row = 2;

        // --- 検索条件から売上日の得意先を取得 ---
        $start_customer_code = MasterCustomer::query()->where('id', $searchConditions['customer_id']['start'])->value('code');
        $end_customer_code = MasterCustomer::query()->where('id', $searchConditions['customer_id']['end'])->value('code');

        // --- 検索条件から売上日の商品を取得 ---
        $start_product_code = MasterProduct::query()->where('id', $searchConditions['product_id']['start'])->value('code');
        $end_product_code = MasterProduct::query()->where('id', $searchConditions['product_id']['end'])->value('code');

        // --- 検索条件から売上日の期間を取得 ---
        $start_date = $searchConditions['sales_date']['start'] ?? '';
        $end_date = $searchConditions['sales_date']['end'] ?? $start_date;

        // --- 表示用に日付整形 ---
        $start_date_str = $start_date ? new Carbon($start_date)->format('Y/m/d') : '';
        $end_date_str = $end_date ? new Carbon($end_date)->format('Y/m/d') : '';
        $today_str = now()->format('Y/m/d');

        // --- 得意先と商品が未選択の場合のデフォルト表示 ---
        $start_customer_display = $start_customer_code;
        if (empty($start_customer_code)) {
            $start_customer_display = '未選択';
        }
        $end_customer_display = $end_customer_code;
        if (empty($end_customer_code)) {
            $end_customer_display = '未選択';
        }

        $start_product_display = $start_product_code;
        if (empty($start_product_code)) {
            $start_product_display = '未選択';
        }

        $end_product_display = $end_product_code;
        if (empty($end_product_code)) {
            $end_product_display = '未選択';
        }

        $header_text = "得意先[{$start_customer_display}]-[{$end_customer_display}] 商　品[{$start_product_display}]-[{$end_product_display}] 売上日[{$start_date_str}]-[{$end_date_str}]  ＊＊ 得意先別商品別日別売上集計表 ＊＊ DATE : {$today_str}";
        $activeSheet->setCellValue('A1', $header_text);

        // A2～Q2 の罫線
        $activeSheet->getStyle('A2:Q2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // 列幅自動調整
        foreach (range('B', 'Q') as $columnID) {
            $activeSheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // --- A2～Q2 に表示させる文字列を作成 ---
        $activeSheet->setCellValue('A2', '部門CD');
        $activeSheet->setCellValue('B2', '部門名');
        $activeSheet->setCellValue('C2', '得意先CD');
        $activeSheet->setCellValue('D2', '得意先名');
        $activeSheet->setCellValue('E2', '商品CD');
        $activeSheet->setCellValue('F2', '商品名');
        $activeSheet->setCellValue('G2', '規格名');
        $activeSheet->setCellValue('H2', '売上日');
        $activeSheet->setCellValue('I2', '単価');
        $activeSheet->setCellValue('J2', '売上金額');
        $activeSheet->setCellValue('K2', '返品金額');
        $activeSheet->setCellValue('L2', '値引金額');
        $activeSheet->setCellValue('M2', '粗利金額');
        $activeSheet->setCellValue('N2', '粗利率');
        $activeSheet->setCellValue('O2', '売上数量');
        $activeSheet->setCellValue('P2', '返品数量');
        $activeSheet->setCellValue('Q2', '粗純売上数量');

        // データ行
        $current_row = $start_row + 1;
        $department_name = '全部門';
        foreach ($outputData as $row) {
            $activeSheet->setCellValue("B{$current_row}", $department_name);
            $activeSheet->setCellValue("C{$current_row}", $row['customer_code']);
            $activeSheet->setCellValue("D{$current_row}", $row['customerName']);
            $activeSheet->setCellValue("E{$current_row}", $row['product_code']);
            $activeSheet->setCellValue("F{$current_row}", $row['product_name']);
            $activeSheet->setCellValue("H{$current_row}", Carbon::parse($row['order_date'])->format('Y/m/d'));
            $activeSheet->setCellValue("I{$current_row}", $row['unit_price']);
            $activeSheet->setCellValue("J{$current_row}", $row['total_sales_sub_total']);
            $activeSheet->setCellValue("K{$current_row}", $row['total_return_sub_total']);
            $activeSheet->setCellValue("L{$current_row}", $row['total_discount']);
            $activeSheet->setCellValue("M{$current_row}", $row['total_gross_profit']);
            $activeSheet->setCellValue("N{$current_row}", $row['gross_profit_rate']);
            $activeSheet->setCellValue("O{$current_row}", $row['total_sales_quantity']);
            $activeSheet->setCellValue("P{$current_row}", $row['total_return_quantity']);
            $activeSheet->setCellValue("Q{$current_row}", $row['net_sales_quantity']);

            // 罫線
            $activeSheet->getStyle("A{$current_row}:Q{$current_row}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            ++$current_row;
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
            ->select(
                'm_customers.code AS customer_code',
                'm_customers.name AS customerName',
                'm_products.code AS product_code',
                'm_products.name AS product_name',
                'sales_orders.order_date',
                'sales_order_details.unit_price',
                DB::raw('
                        SUM(CASE WHEN sales_orders.sales_classification_id = 0 THEN sales_order_details.sub_total ELSE 0 END)
                        AS total_sales_sub_total
                    '),
                DB::raw('SUM(CASE WHEN sales_orders.sales_classification_id = 1 THEN sales_order_details.sub_total ELSE 0 END) AS total_return_sub_total'),
                DB::raw('SUM(sales_order_details.discount) AS total_discount'),
                DB::raw('SUM(sales_order_details.gross_profit) AS total_gross_profit'),
                DB::raw('
                        CASE
                            WHEN SUM(
                                CASE
                                    WHEN sales_orders.sales_classification_id = 0 THEN sales_order_details.sub_total
                                    WHEN sales_orders.sales_classification_id = 1 THEN sales_order_details.sub_total ELSE 0 END) = 0 THEN 0
                            ELSE ROUND(
                                 SUM(sales_order_details.gross_profit) / NULLIF(
                                     SUM(CASE WHEN sales_orders.sales_classification_id = 0 THEN sales_order_details.sub_total
                                              WHEN sales_orders.sales_classification_id = 1 THEN sales_order_details.sub_total ELSE 0 END), 0) * 100, 2) END AS gross_profit_rate'),
                DB::raw('SUM(CASE WHEN sales_orders.sales_classification_id = 0 THEN sales_order_details.quantity ELSE 0 END) AS total_sales_quantity'),
                DB::raw('SUM(CASE WHEN sales_orders.sales_classification_id = 1 THEN sales_order_details.quantity ELSE 0 END) AS total_return_quantity'),
                DB::raw('SUM(CASE WHEN sales_orders.sales_classification_id = 0 THEN sales_order_details.quantity
                                        WHEN sales_orders.sales_classification_id = 1 THEN sales_order_details.quantity ELSE 0 END) AS net_sales_quantity'),
            )
            ->join('sales_order_details', 'sales_orders.id', '=', 'sales_order_details.sales_order_id')
            ->join('m_customers', 'sales_orders.customer_id', '=', 'm_customers.id')
            ->join('m_products', 'sales_order_details.product_id', '=', 'm_products.id')
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
            ->when($searchConditions !== null, function ($query) use ($searchConditions) {
                $start_customer = $searchConditions['customer_id']['start'] ?? null;
                $end_customer = $searchConditions['customer_id']['end'] ?? null;

                if (is_null($start_customer) && is_null($end_customer)) {
                    return $query;
                }
                if (isset($start_customer) && is_null($end_customer)) {
                    return $query->where('sales_orders.customer_id', '>=', $start_customer);
                }
                if (is_null($start_customer) && isset($end_customer)) {
                    return $query->where('sales_orders.customer_id', '<=', $end_customer);
                }

                return $query->whereBetween('sales_orders.customer_id', [$start_customer, $end_customer]);
            })
            ->when($searchConditions !== null, function ($query) use ($searchConditions) {
                $start_product = $searchConditions['product_id']['start'] ?? null;
                $end_product = $searchConditions['product_id']['end'] ?? null;

                if (is_null($start_product) && is_null($end_product)) {
                    return $query;
                }
                if (isset($start_product) && is_null($end_product)) {
                    return $query->where('sales_order_details.product_id', '>=', $start_product);
                }
                if (is_null($start_product) && isset($end_product)) {
                    return $query->where('sales_order_details.product_id', '<=', $end_product);
                }

                return $query->whereBetween('sales_order_details.product_id', [$start_product, $end_product]);
            })
            ->where('sales_orders.sales_classification_id', '<=', SalesClassification::CLASSIFICATION_RETURN)
            ->groupBy(
                'sales_orders.order_date',
                'sales_orders.customer_id',
                'm_customers.name',
                'sales_order_details.product_id',
                'm_products.name',
                'sales_order_details.unit_price'
            )
            ->orderBy('sales_orders.order_date')
            ->get()
            ->toArray();
    }
}
