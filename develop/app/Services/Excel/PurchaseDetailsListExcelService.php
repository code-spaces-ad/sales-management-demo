<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Enums\PurchaseClassification;
use App\Models\Trading\PurchaseOrder;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use TransactionType;

class PurchaseDetailsListExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 8;

    /** 明細行の最大行 */
    protected int $max_row_detail = 18;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.purchase_details_list')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.purchase_details_list')
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
            . config('consts.excel.template_file.purchase_details_list')
        ), PageSetup::ORIENTATION_LANDSCAPE);

        $start_row = 2;

        // --- 検索条件から売上日の期間を取得 ---
        $start = $searchConditions['purchase_date']['start'] ?? '';
        $end = $searchConditions['purchase_date']['end'] ?? '';

        // --- 表示用に日付整形 ---
        $start_date_str = $start ? new Carbon($start)->format('Y/m/d') : '';
        $end_date_str = $end ? new Carbon($end)->format('Y/m/d') : '';
        $today_str = now()->format('Y/m/d');

        // --- A1 に表示させる文字列を作成 ---
        $headerText = "売上日[{$start_date_str}]-[{$end_date_str}] ＊＊  仕入明細一覧(入荷日指定) ＊＊ DATE : {$today_str}";
        $activeSheet->setCellValue('A1', $headerText);

        $activeSheet->getColumnDimension('A')->setWidth(12); // 幅の調整

        // A2～R2 の罫線
        $activeSheet->getStyle('A2:R2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // 列幅自動調整
        foreach (range('B', 'R') as $columnID) {
            $activeSheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // --- A2～R2 に表示させる文字列を作成 ---
        $activeSheet->setCellValue('A2', '入荷日');
        $activeSheet->setCellValue('B2', '伝票No');
        $activeSheet->setCellValue('C2', '行No');
        $activeSheet->setCellValue('D2', '伝区');
        $activeSheet->setCellValue('E2', '仕入先CD');
        $activeSheet->setCellValue('F2', '仕入先名');
        $activeSheet->setCellValue('G2', '商品CD');
        $activeSheet->setCellValue('H2', '商品名');
        $activeSheet->setCellValue('I2', '経理コード');
        $activeSheet->setCellValue('J2', '経理名');
        $activeSheet->setCellValue('K2', '倉庫CD');
        $activeSheet->setCellValue('L2', '倉庫名');
        $activeSheet->setCellValue('M2', '仕区');
        $activeSheet->setCellValue('N2', '入数');
        $activeSheet->setCellValue('O2', '箱数');
        $activeSheet->setCellValue('P2', '数量');
        $activeSheet->setCellValue('Q2', '単価');
        $activeSheet->setCellValue('R2', '金額');

        // データ行
        $current_row = $start_row + 1;
        $line_number = 1;
        foreach ($outputData as $row) {
            $activeSheet->setCellValue("A{$current_row}", $row['order_date']);
            $activeSheet->setCellValue("B{$current_row}", $row['order_number']);
            $activeSheet->setCellValue("C{$current_row}", $line_number);
            $activeSheet->setCellValue("D{$current_row}", $row['transaction_type_id']
                . ' ' . TransactionType::getDescription($row['transaction_type_id']));
            $activeSheet->setCellValue("E{$current_row}", $row['suppliers_code']);
            $activeSheet->setCellValue("F{$current_row}", $row['suppliers_name']);
            $activeSheet->setCellValue("G{$current_row}", $row['products_code']);
            $activeSheet->setCellValue("H{$current_row}", $row['products_name']);
            $activeSheet->setCellValue("I{$current_row}", $row['accounting_codes']);
            $activeSheet->setCellValue("J{$current_row}", $row['accounting_codes_name']);
            $activeSheet->setCellValue("K{$current_row}", $row['warehouses_code']);
            $activeSheet->setCellValue("L{$current_row}", $row['warehouses_name']);
            $activeSheet->setCellValue("M{$current_row}", $row['purchase_classification_id']
                . ' ' . PurchaseClassification::getDescription($row['purchase_classification_id']));
            $activeSheet->setCellValue("P{$current_row}", $row['quantity']);
            $activeSheet->setCellValue("Q{$current_row}", $row['unit_price']);
            $activeSheet->setCellValue("R{$current_row}", $row['sub_total']);

            // 罫線
            $activeSheet->getStyle("A{$current_row}:R{$current_row}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            ++$current_row;
            ++$line_number;
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
        return PurchaseOrder::query()
            ->select([
                'purchase_orders.order_date',
                'purchase_orders.order_number',
                'purchase_orders.purchase_classification_id',
                'purchase_orders.transaction_type_id',
                'm_suppliers.code AS suppliers_code',
                'm_suppliers.name AS suppliers_name',
                'm_products.code AS products_code',
                'm_products.name AS products_name',
                'm_accounting_codes.code AS accounting_codes',
                'm_accounting_codes.name AS accounting_codes_name',
                'm_warehouses.code AS warehouses_code',
                'm_warehouses.name AS warehouses_name',
                'purchase_order_details.quantity',
                'purchase_order_details.unit_price',
                'purchase_order_details.sub_total',
            ])
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
            ->join('m_suppliers', 'purchase_orders.supplier_id', '=', 'm_suppliers.id')
            ->join('m_products', 'purchase_order_details.product_id', '=', 'm_products.id')
            ->leftjoin('m_accounting_codes', 'm_products.accounting_code_id', '=', 'm_accounting_codes.id')
            ->join('m_warehouses', 'purchase_orders.office_facilities_id', '=', 'm_warehouses.id')
            // 伝票日付で絞り込み
            ->when($searchConditions !== null, function ($query) use ($searchConditions) {
                $start_date = $searchConditions['purchase_date']['start'] ?? null;
                $end_date = $searchConditions['purchase_date']['end'] ?? null;

                if (is_null($start_date) && is_null($end_date)) {
                    return $query;
                }
                if (isset($start_date) && is_null($end_date)) {
                    return $query->where('purchase_orders.order_date', '>=', $start_date);
                }
                if (is_null($start_date) && isset($end_date)) {
                    return $query->where('purchase_orders.order_date', '<=', $end_date);
                }

                return $query->whereBetween('purchase_orders.order_date', [$start_date, $end_date]);
            })
            ->orderBy('purchase_orders.order_date')
            ->orderBy('purchase_orders.order_number')
            ->get()
            ->toArray();

    }
}
