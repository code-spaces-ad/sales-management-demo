<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Enums\TransactionType;
use App\Models\Master\MasterAccountingCode;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AccountsPayableListByExpenseCodeExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 8;

    /** 明細行の最大行 */
    protected int $max_row_detail = 18;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.accounts_payable_list_by_expense_code')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.accounts_payable_list_by_expense_code')
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
            . config('consts.excel.template_file.accounts_payable_list_by_expense_code')
        ), PageSetup::ORIENTATION_LANDSCAPE);

        $start_row = 3;

        // 年月度の表示
        $date = DateTime::createFromFormat('Y-m', $searchConditions['year_month']);
        $formattedDate = $date->format('Y年n月分');

        // タイトル
        $activeSheet->setCellValue('A1', '経費コード別買掛金一覧　' . $formattedDate);
        $activeSheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM);

        // 見出しの1行目
        $activeSheet->setCellValue('A2', '経費コード');
        $activeSheet->setCellValue('B2', '科目名');
        $activeSheet->setCellValue('C2', '金額');
        $activeSheet->setCellValue('F2', '備考');

        // 見出しの2行目 ※金額の内訳
        $activeSheet->setCellValue('C3', '合計');
        $activeSheet->setCellValue('D3', '8%');
        $activeSheet->setCellValue('E3', '10%');

        // セル結合
        $activeSheet->mergeCells('A2:A3'); // 経費コード
        $activeSheet->mergeCells('B2:B3'); // 科目名
        $activeSheet->mergeCells('C2:E2'); // 金額
        $activeSheet->mergeCells('F2:F3'); // 備考

        // セル中央寄せ
        $activeSheet->getStyle('A2:F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $activeSheet->getStyle('A2:F3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // 罫線(インデックス)
        $activeSheet->getStyle('A2:F3')
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // インデックスの下に二重線
        $activeSheet->getStyle('A3:F3')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        // 行の高さの設定
        $activeSheet->getRowDimension(1)->setRowHeight(39);

        // 列幅を固定
        $activeSheet->getColumnDimension('A')->setWidth(12);
        $activeSheet->getColumnDimension('C')->setWidth(17);
        $activeSheet->getColumnDimension('D')->setWidth(17);
        $activeSheet->getColumnDimension('E')->setWidth(17);
        $activeSheet->getColumnDimension('F')->setWidth(15);

        // 列幅の自動調整
        $activeSheet->getColumnDimension('B')->setAutoSize(true);

        // データ行
        $current_row = $start_row + 1;
        $sum_total = $sum_reduced = $sum_regular = 0;
        foreach ($outputData as $row) {
            $accounting_code = MasterAccountingCode::query()
                ->where('id', $row['accounting_code_id'])
                ->value('code');
            $activeSheet->setCellValue("A{$current_row}", $accounting_code);
            $activeSheet->setCellValue("B{$current_row}", $row['accounting_code_name']);
            $activeSheet->setCellValue("C{$current_row}", $row['total_sub_total']);
            $activeSheet->setCellValue("D{$current_row}", $row['total_reduced_tax_sub_total']);
            $activeSheet->setCellValue("E{$current_row}", $row['total_regular_tax_sub_total']);

            // 罫線
            $activeSheet->getStyle("A{$current_row}:F{$current_row}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // 合計加算
            $sum_total += $row['total_sub_total'];
            $sum_reduced += $row['total_reduced_tax_sub_total'];
            $sum_regular += $row['total_regular_tax_sub_total'];

            ++$current_row;
        }

        // (3) 合計行
        $sum_row = $current_row;
        $borderRow = $sum_row - 1;

        // 合計行の上に二重線
        $activeSheet->getStyle("A{$borderRow}:F{$borderRow}")
            ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        // 合計行のデータ
        $activeSheet->setCellValue("A{$sum_row}", '合計');
        $activeSheet->setCellValue("C{$sum_row}", $sum_total);
        $activeSheet->setCellValue("D{$sum_row}", $sum_reduced);
        $activeSheet->setCellValue("E{$sum_row}", $sum_regular);

        // 右寄せ
        $activeSheet->getStyle("A{$sum_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // 合計行の罫線
        $activeSheet->getStyle("A{$sum_row}:F{$sum_row}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
        // 出力データの取得
        return MasterAccountingCode::query()
            ->select(
                'm_accounting_codes.id as accounting_code_id',
                'm_accounting_codes.name as accounting_code_name',
                DB::raw('SUM(purchase_order_details.sub_total) as total_sub_total'),
                DB::raw('SUM(CASE WHEN purchase_order_details.reduced_tax_flag = 0
                                          AND purchase_order_details.consumption_tax_rate > 0
                                         THEN purchase_order_details.sub_total ELSE 0 END) as total_regular_tax_sub_total'),
                DB::raw('SUM(CASE WHEN purchase_order_details.reduced_tax_flag = 1
                                         THEN purchase_order_details.sub_total ELSE 0 END) as total_reduced_tax_sub_total'),
            )
            ->Join('m_products', 'm_products.accounting_code_id', '=', 'm_accounting_codes.id')
            ->Join('purchase_order_details', 'purchase_order_details.product_id', '=', 'm_products.id')
            ->Join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')

            // 伝票日付で絞り込み
            ->when($searchConditions !== null, function ($query) use ($searchConditions) {
                $start_date = $searchConditions['start_date'] ?? null;
                $end_date = $searchConditions['end_date'] ?? null;

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
            ->where('m_accounting_codes.output_group', 1)
            ->where('purchase_orders.transaction_type_id', TransactionType::ON_ACCOUNT)
            ->whereNull('purchase_orders.deleted_at')
            ->groupBy('m_accounting_codes.id', 'm_accounting_codes.name')
            ->get()
            ->toArray();
    }
}
