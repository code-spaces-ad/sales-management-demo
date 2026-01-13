<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Consts\ReportOutput\ReducedTaxFlagConst;
use App\Consts\ReportOutput\TransactionsTypeNameConst;
use App\Enums\DepositMethodType;
use App\Enums\TransactionType;
use App\Models\Invoice\ChargeData;
use App\Models\Master\MasterCustomer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerLedgerEmployeeExcelService extends AbstractExcelService
{
    /** 繰越残高の行位置 */
    protected int $balance_row_detail = 10;

    /** 明細行の開始位置 */
    protected int $start_row_detail = 11;

    /** 明細行の終了位置 */
    protected int $end_row_detail = 36;

    /** 売上合計の行位置 */
    protected int $sales_row_detail = 38;

    /** 消費税合計の行位置 */
    protected int $tax_row_detail = 39;

    /** 入金合計の行位置 */
    protected int $deposit_row_detail = 41;

    /** 1ページの最大明細行数・残高の行位置 */
    protected int $max_row_detail = 42;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.customer_ledger_by_employee')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.customer_ledger_by_employee')
            . $prefix . config('consts.pdf.filename.file_extension');

        parent::__construct($downloadExcelFileName, $downloadPdfFileName);
    }

    /**
     * Excelデータ作成
     *
     * @param array $searchConditions 検索条件
     * @param array $outputData 出力データ
     * @param bool $isPdf PDF出力フラグ
     * @return Spreadsheet
     *
     * @throws Exception
     */
    public function getSpreadSheet(array $searchConditions, array $outputData, bool $isPdf = false): Spreadsheet
    {
        $firstSheet = true;

        foreach ($outputData as $sheetIndex => $customerData) {

            if ($firstSheet) {
                // 最初のシートを作成
                $activeSheet = $this->initSpreadSheet(storage_path(
                    config('consts.excel.template_path') . config('consts.excel.template_file.customer_ledger_by_employee')
                ), PageSetup::ORIENTATION_LANDSCAPE);

                // シート名を設定
                $activeSheet->setTitle($this->customerSheetName($customerData['customer'], $sheetIndex + 1));
                $firstSheet = false;
            }

            if (!$firstSheet && $sheetIndex > 0) {
                // 最初のシートを取得して、コピー(次シート)用として保持
                $nextSheet = $this->spreadSheet->getSheet(0);

                // 最初のシートをコピー
                $activeSheet = $nextSheet->copy();

                // シート名を設定
                $activeSheet->setTitle($this->customerSheetName($customerData['customer'], $sheetIndex + 1));

                // スプレッドシートに追加
                $this->spreadSheet->addSheet($activeSheet);

                // 明細行のデータ部分をクリア
                $this->clearCellData($activeSheet);

                // 改ページをリセット
                $this->resetPageBreaks($activeSheet);

                // 行番号をリセット
                $this->resetRowNumbers();
            }

            // ヘッダー情報の設定
            $this->setHeader($activeSheet, $customerData);

            // 繰越残高(今回請求額)の出力
            $this->setCarryOver($activeSheet, $customerData['charge_total']);

            // 明細行の出力
            $this->setDetails($activeSheet, $customerData['ledger_data']);

            // 売上合計行の出力
            $this->setSalesTotalRow($activeSheet, $customerData['sales_total'], $customerData['tax_total']);

            // 消費税合計行の出力
            $this->setTaxes($activeSheet, $customerData['tax_totals']);

            // 入金合計行の出力
            $this->setDepositTotalRow($activeSheet, $customerData['deposit_total']);

            // 残高行を出力
            $this->setBalanceRow($activeSheet, $customerData['balance_total']);
        }

        return $this->spreadSheet;
    }

    /**
     * Excelシート名を生成
     *
     * @param array $customer 得意先情報
     * @param int $sheetNumber シート番号
     * @return string シート名
     */
    private function customerSheetName(array $customer, int $sheetNumber): string
    {
        // 得意先名が長い場合は短縮（Excelのシート名制限対応）
        $customerName = mb_substr($customer['name'], 0, 20);

        return "{$sheetNumber}_{$customerName}";
    }

    /**
     * $max_row_detailを超えるデータ範囲をクリア
     *
     * @param Worksheet $sheet
     * @return void
     */
    private function clearCellData(Worksheet $sheet): void
    {
        // 明細行のセルの値を削除
        for ($row = 10; $row <= 42; ++$row) {
            for ($col = 'A'; $col <= 'T'; ++$col) {
                $sheet->setCellValue($col . $row, '');
            }
        }

        // 追加された行があれば削除（templateの設定行数の42行に戻す）
        $currentRow = $sheet->getHighestRow();
        if ($currentRow > 42) {
            $addRows = $currentRow - 42;
            $sheet->removeRow(43, $addRows);  // 43行目以降の行を削除
        }

        // スタイルの設定(太い実線)
        $sheet->getStyle('A42:T42')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
    }

    /**
     * 改ページのリセット
     *
     * @param Worksheet $sheet
     * @return void
     */
    private function resetPageBreaks(Worksheet $sheet): void
    {
        // 1ページを超えない明細行の出力データのシートは改ページをリセットする
        $sheet->getPageSetup()->setPrintArea('A1:T42');
        $sheet->getPageSetup()->setFitToPage(true);
    }

    /**
     * 行番号をリセット
     * 明細行の開始位置や終了位置を初期化
     *
     * @return void
     */
    private function resetRowNumbers(): void
    {
        $this->end_row_detail = 36;
        $this->sales_row_detail = 38;
        $this->tax_row_detail = 39;
        $this->deposit_row_detail = 41;
        $this->max_row_detail = 42;
    }

    /**
     * 必要な行を挿入し、行番号を調整
     *
     * @param Worksheet $sheet
     * @param int $startRow 挿入を開始する行番号
     * @param int $addRows 追加する行数
     * @return void
     */
    private function setAddRows(Worksheet $sheet, int $startRow, int $addRows): void
    {
        // 明細行の終了位置から行を挿入
        $sheet->insertNewRowBefore($this->end_row_detail + 1, $addRows);

        // 挿入された行を2行1組として処理
        for ($i = 0; $i < $addRows; ++$i) {
            $targetRow = $startRow + $i;
            $this->setFormatCells($sheet, $targetRow);

            // 最初の行だけ上下罫線を消す処理
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$targetRow}:T{$targetRow}")->getBorders()->getTop()->setBorderStyle(
                    Border::BORDER_NONE
                );
                $sheet->getStyle("A{$targetRow}:T{$targetRow}")->getBorders()->getBottom()->setBorderStyle(
                    Border::BORDER_NONE
                );
            }
        }

        // 行を挿入したことによる行番号の調整
        if ($startRow <= $this->end_row_detail) {
            $this->end_row_detail += $addRows;
        }
        if ($startRow <= $this->sales_row_detail) {
            $this->sales_row_detail += $addRows;
        }
        if ($startRow <= $this->tax_row_detail) {
            $this->tax_row_detail += $addRows;
        }
        if ($startRow <= $this->deposit_row_detail) {
            $this->deposit_row_detail += $addRows;
        }
        if ($startRow <= $this->max_row_detail) {
            $this->max_row_detail += $addRows;
        }
    }

    /**
     * 指定された行のセルを結合し、書式を設定
     *
     * @param Worksheet $sheet
     * @param int $row 対象行番号
     * @return void
     */
    private function setFormatCells(Worksheet $sheet, int $row): void
    {
        // D～I列を結合
        $sheet->mergeCells("D{$row}:I{$row}");

        $sheet->getStyle("D{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }

    /**
     * ヘッダー情報を設定
     *
     * @param Worksheet $sheet
     * @param array $outputData 出力データ
     * @return void
     */
    private function setHeader(Worksheet $sheet, array $outputData): void
    {
        $customer = $outputData['customer'];
        $employeeName = $outputData['employee_name'];
        $period = $outputData['period'];

        // 日付範囲をフォーマット
        $dateRange = $this->formatDateRange($period['start_date'] ?? null, $period['end_date'] ?? null);

        // ヘッダー情報を設定
        $sheet->setCellValue('H2', $customer['code'] . ' : ' . $customer['name']);
        $sheet->getStyle('H2')->getFont()->setSize(18);
        $sheet->setCellValue('T3', Carbon::now()->format('Y/m/d'));
        $sheet->setCellValue('B1', $employeeName);
        $sheet->getStyle('B1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->setCellValue('B3', $customer['address']);
        $sheet->setCellValue('B4', $customer['tel_number']);
        $sheet->setCellValue('B5', $dateRange);
    }

    /**
     * 繰越残高を出力
     *
     * @param Worksheet $sheet
     * @param int $carryOver 繰越残高
     * @return void
     */
    private function setCarryOver(Worksheet $sheet, int $carryOver): void
    {
        $row = $this->balance_row_detail;
        $sheet->setCellValue('D' . $row, '【前月　残高】');
        $sheet->setCellValue('P' . $row, $carryOver);
        $this->setNumberFormat($sheet, 'P', $row, $row);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * 明細行の行数計算と出力
     *
     * @param Worksheet $sheet
     * @param array $ledgerData 明細データ
     * @return void
     */
    private function setDetails(Worksheet $sheet, array $ledgerData): void
    {
        $row = $this->start_row_detail;
        $runningBalance = 0;

        // 売上伝票番号と入金伝票番号ごとにデータをグループ化
        $groupedData = collect($ledgerData)->groupBy(function ($item) {
            return $item->transaction_type . '_' . $item->transaction_number;
        });

        // 必要総行数の計算
        $totalRows = 0;
        foreach ($groupedData as $group) {
            $depositMethods = 0;
            foreach ($group as $data) {
                if ($data->transaction_type !== TransactionsTypeNameConst::TRANSACTION_TYPE_NAME_DEPOSIT) {
                    continue;
                }
                foreach (DepositMethodType::asSelectArray() as $method => $methodName) {
                    $methodType = DepositMethodType::getDepositType($method);
                    $colName = 'amount_' . $methodType;
                    if (property_exists($data, $colName) && $data->$colName > 0) {
                        ++$depositMethods;
                    }
                }
            }

            // 入金方法ごとの必要行数を計算
            $groupRows = count($group) * 2;  // 各データの行
            $groupRows += ($depositMethods > 0) ? ($depositMethods - count($group)) * 2 : 0; // 入金方法の行
            $groupRows += 2;  // 伝票計の行（8%と10%）

            $totalRows += $groupRows;
        }

        // データ量が'end_row_detail'以上になる場合に行を挿入
        $setRows = $this->end_row_detail - $this->start_row_detail + 1;
        if ($totalRows > $setRows) {
            $addRows = $totalRows - $setRows;
            // excelシートの最終行に行を挿入
            $this->setAddRows($sheet, $this->end_row_detail + 1, $addRows);
            // 挿入後に'end_row_detail'を更新
            $this->end_row_detail += $addRows;
        }

        $this->setGroupedData($sheet, $groupedData, $row, $runningBalance);
    }

    /**
     * グループ化されたデータを出力
     *
     * @param Worksheet $sheet
     * @param Collection $groupedData グループ化されたデータ
     * @param int &$row 現在の行番号（参照渡し）
     * @param int &$runningBalance 現在の残高（参照渡し）
     * @return void
     */
    private function setGroupedData(Worksheet $sheet, Collection $groupedData, int &$row, int &$runningBalance): void
    {
        foreach ($groupedData as $group) {
            $groupTax8 = 0;
            $groupTax10 = 0;

            // 明細行を出力
            foreach ($group as $data) {
                $data = (object) $data;
                $runningBalance += $data->sub_total - $data->deposit_amount;

                // 税率ごとの合計を計算
                if ($data->reduced_tax_flag == ReducedTaxFlagConst::REDUCED_TAX_FLAG_REDUCED) {
                    $groupTax8 += $data->sub_total;
                }
                if ($data->reduced_tax_flag == ReducedTaxFlagConst::REDUCED_TAX_FLAG_NOMAL) {
                    $groupTax10 += $data->sub_total;
                }

                // 入金と売上で処理を分岐
                if ($data->transaction_type === TransactionsTypeNameConst::TRANSACTION_TYPE_NAME_DEPOSIT) {
                    $this->setDeposit($sheet, $data, $row);
                }
                if ($data->transaction_type === TransactionsTypeNameConst::TRANSACTION_TYPE_NAME_SALES) {
                    $this->setSales($sheet, $data, $row);
                }
            }

            // 【伝票計】8%の行を挿入
            $this->setOutputRow($sheet, $row, [
                'D' => '【伝　票　計】',
                'J' => '8%',
                'M' => $groupTax8 > 0 ? $groupTax8 : '',
                'P' => $runningBalance,
            ]);
            $this->setNumberFormat($sheet, 'M', $row, $row);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            ++$row;

            // 【伝票計】10%の行を挿入
            $this->setOutputRow($sheet, $row, [
                'D' => '【伝　票　計】',
                'J' => '10%',
                'M' => $groupTax10 > 0 ? $groupTax10 : '',
                'P' => $runningBalance,
            ]);
            $this->setNumberFormat($sheet, 'M', $row, $row);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            ++$row;
        }
    }

    /**
     * 明細の入金行を出力
     *
     * @param Worksheet $sheet
     * @param object $data 明細データ
     * @param int &$row 現在の行番号（参照渡し）
     * @return void
     */
    private function setDeposit(Worksheet $sheet, $data, int &$row): void
    {
        foreach (DepositMethodType::asSelectArray() as $method => $methodName) {
            $methodType = DepositMethodType::getDepositType($method);
            $colName = 'amount_' . $methodType;

            // 除外条件
            if (!property_exists($data, $colName) || $data->$colName <= 0) {
                continue;
            }

            // 伝票日付と商品コードの行を表示
            $this->setOutputRow($sheet, $row, [
                'A' => Carbon::parse($data->transaction_date)->format('Y/m/d'),
                'D' => $data->product_id ?? '',
            ]);

            // セルの結合と書式設定
            $this->setFormatCells($sheet, $row);
            ++$row;

            // 取引種別名を取得
            $transactionTypeName = TransactionType::asSelectArray()[$data->transaction_type_id] ?? '';

            // 入金方法ごとに行を出力
            $this->setOutputRow($sheet, $row, [
                'A' => $data->transaction_number ?? '',
                'B' => $transactionTypeName,
                'D' => $methodName, // 入金方法名
                'O' => $data->$colName, // 入金金額
            ]);

            $this->setNumberFormat($sheet, 'O', $row, $row);
            ++$row;
        }
    }

    /**
     * 明細の売上行を出力
     *
     * @param Worksheet $sheet
     * @param object $data 明細データ
     * @param int &$row 現在の行番号（参照渡し）
     * @return void
     */
    private function setSales(Worksheet $sheet, $data, int &$row): void
    {
        // 伝票日付と商品コードの行を表示
        $this->setOutputRow($sheet, $row, [
            'A' => Carbon::parse($data->transaction_date)->format('Y/m/d'),
            'D' => $data->product_id ?? '',
        ]);

        // セルの結合と書式設定
        $this->setFormatCells($sheet, $row);
        ++$row;

        // 取引種別名を取得
        $transactionTypeName = TransactionType::asSelectArray()[$data->transaction_type_id] ?? '';

        // 売上データを出力
        $this->setOutputRow($sheet, $row, [
            'A' => $data->transaction_number ?? '',
            'B' => $transactionTypeName,
            'C' => $data->branch_name ?? '',
            'D' => $data->product_name ?? '',
            'J' => $data->quantity ?? '',
            'K' => $data->unit_name ?? '',
            'L' => $data->unit_price ?? '',
            'M' => $data->sub_total ?? '',
        ]);
        $this->setNumberFormat($sheet, 'M', $row, $row);
        ++$row;
    }

    /**
     * 売上合計行を出力
     *
     * @param Worksheet $sheet
     * @param int $salesTotal 売上合計データ
     * @param int $taxTotal 消費税合計データ
     * @return void
     */
    private function setSalesTotalRow(Worksheet $sheet, int $salesTotal, int $taxTotal): void
    {
        $row = $this->sales_row_detail;
        $sheet->setCellValue('D' . $row, '【売上　合計】');
        $sheet->setCellValue('M' . $row, $salesTotal);
        $sheet->setCellValue('N' . $row, $taxTotal);
        $this->setNumberFormat($sheet, 'M', $row, $row);
        $this->setNumberFormat($sheet, 'N', $row, $row);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * 消費税合計行を出力
     *
     * @param Worksheet $sheet
     * @param array $taxTotals 税率ごとの売上合計・消費税合計データ
     * @return void
     */
    private function setTaxes(Worksheet $sheet, array $taxTotals): void
    {
        $row = $this->tax_row_detail;

        // 税率8%の売上合計と消費税合計を出力
        $sheet->setCellValue('D' . $row, '【売上　合計】');
        $sheet->setCellValue('J' . $row, '8%');
        $sheet->setCellValue('M' . $row, $taxTotals['tax8'] ?? 0);
        $sheet->setCellValue('N' . $row, ($taxTotals['tax8'] ?? 0) * 0.08);
        $this->setNumberFormat($sheet, 'M', $row, $row);
        $this->setNumberFormat($sheet, 'N', $row, $row);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        ++$row;

        // 税率10%の売上合計と消費税合計を出力
        $sheet->setCellValue('D' . $row, '【売上　合計】');
        $sheet->setCellValue('J' . $row, '10%');
        $sheet->setCellValue('M' . $row, $taxTotals['tax10'] ?? 0);
        $sheet->setCellValue('N' . $row, ($taxTotals['tax10'] ?? 0) * 0.10);
        $this->setNumberFormat($sheet, 'M', $row, $row);
        $this->setNumberFormat($sheet, 'N', $row, $row);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * 入金合計行を出力
     *
     * @param Worksheet $sheet
     * @param int $depositTotal 入金
     * @return void
     */
    private function setDepositTotalRow(Worksheet $sheet, int $depositTotal): void
    {
        $row = $this->deposit_row_detail;
        $sheet->setCellValue('D' . $row, '【入金　合計】');
        $sheet->setCellValue('O' . $row, $depositTotal);
        $this->setNumberFormat($sheet, 'O', $row, $row);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * 残高行を出力
     *
     * @param Worksheet $sheet
     * @param int $balanceTotal 残高
     * @return void
     */
    private function setBalanceRow(Worksheet $sheet, int $balanceTotal): void
    {
        $row = $this->max_row_detail;
        $sheet->setCellValue('D' . $row, '【当月　残高】');
        $sheet->setCellValue('P' . $row, $balanceTotal);
        $this->setNumberFormat($sheet, 'P', $row, $row);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * 日付範囲をフォーマット
     *
     * @param string|null $startDate 開始日
     * @param string|null $endDate 終了日
     * @return string フォーマット済み日付範囲
     */
    private function formatDateRange(?string $startDate, ?string $endDate): string
    {
        $dateRange = '';
        if ($startDate) {
            $dateRange .= Carbon::parse($startDate)->format('Y/m/d');
        }
        $dateRange .= ' 〜 ';
        if ($endDate) {
            $dateRange .= Carbon::parse($endDate)->format('Y/m/d');
        }

        return $dateRange;
    }

    /**
     * 数値セルの書式を設定
     *
     * @param Worksheet $sheet
     * @param string $column 対象列
     * @param int $startRow 開始行
     * @param int $endRow 終了行
     * @return void
     */
    private function setNumberFormat(Worksheet $sheet, string $column, int $startRow, int $endRow): void
    {
        for ($row = $startRow; $row <= $endRow; ++$row) {
            $sheet->getStyle($column . $row)->getNumberFormat()->setFormatCode('#,##0');
        }
    }

    /**
     * 指定された行にデータを出力
     *
     * @param Worksheet $sheet
     * @param int $row 行番号
     * @param array $data 出力データ (列名をキー、値を値として指定)
     * @return void
     */
    private function setOutputRow(Worksheet $sheet, int $row, array $data): void
    {
        foreach ($data as $column => $value) {
            $sheet->setCellValue($column . $row, $value);
        }
    }

    /**
     * 帳票出力データを取得
     *
     * @param array $searchConditions
     * @return array
     */
    public function getOutputData(array $searchConditions): array
    {
        // 出力期間を設定
        $orderDate = $searchConditions['order_date'] ?? [];
        $startDate = $orderDate['start'] ?? null;
        $endDate = $orderDate['end'] ?? null;

        // 得意先範囲を取得
        $startId = $searchConditions['customer_id']['start'] ?? null;
        $endId = $searchConditions['customer_id']['end'] ?? null;

        // 担当者idを取得
        $employeeId = $searchConditions['employee_id'] ?? null;

        // 得意先(担当者)データを取得
        $customers = MasterCustomer::query()
            ->with('employee')
            ->where('employee_id', $employeeId)
            ->when($startId && $endId, fn ($q) => $q->whereBetween('id', [$startId, $endId]))
            ->when($startId && !$endId, fn ($q) => $q->where('id', '>=', $startId))
            ->when(!$startId && $endId, fn ($q) => $q->where('id', '<=', $endId))
            ->get();

        // 得意先データが存在しない場合は空の配列を返す
        if ($customers->isEmpty()) {
            return [];
        }

        // 得意先(担当者)データを初期化
        $customersEmployeeData = [];

        foreach ($customers as $customer) {
            // 繰越残高(今回請求額)を取得
            $search_condition_input_data = [
                'order_date' => [
                    'start' => $searchConditions['order_date']['start'],
                ],
                'customer_id' => $customer->id,
            ];
            $chargeTotal = ChargeData::getLastCarryover($search_condition_input_data);
            $chargeTotalAmount = $chargeTotal->charge_total ?? 0;

            // 売上データと入金データを結合して取得
            $ledgerData = DB::table(function ($query) use ($customer, $startDate, $endDate) {
                // 売上伝票データ
                $query->select(
                    'sales_orders.order_number as transaction_number',
                    'sales_orders.order_date as transaction_date',
                    DB::raw("'" . TransactionsTypeNameConst::TRANSACTION_TYPE_NAME_SALES . "' as transaction_type"),
                    'sales_orders.transaction_type_id',
                    'sales_orders.sales_total as amount',
                    DB::raw('0 as deposit_amount'),
                    'sales_orders.created_at',
                    'sales_order_details.product_id',
                    'sales_order_details.product_name',
                    'sales_order_details.quantity',
                    'sales_order_details.unit_name',
                    'sales_order_details.unit_price',
                    'sales_order_details.sub_total',
                    'sales_order_details.sub_total_tax as tax_amount',
                    'sales_order_details.reduced_tax_flag',
                    'm_branches.name as branch_name',
                    DB::raw('NULL as amount_cash'),
                    DB::raw('NULL as amount_check'),
                    DB::raw('NULL as amount_transfer'),
                    DB::raw('NULL as amount_bill'),
                    DB::raw('NULL as amount_offset'),
                    DB::raw('NULL as amount_discount'),
                    DB::raw('NULL as amount_fee'),
                    DB::raw('NULL as amount_other')
                )
                    ->from('sales_orders')
                    ->join('sales_order_details', 'sales_orders.id', '=', 'sales_order_details.sales_order_id')
                    ->leftJoin('m_branches', 'sales_orders.branch_id', '=', 'm_branches.id')
                    ->where('sales_orders.customer_id', $customer->id)
                    ->when(!is_null($startDate) && !is_null($endDate), function ($q) use ($startDate, $endDate) {
                        return $q->whereBetween('sales_orders.order_date', [$startDate, $endDate]);
                    })
                    ->when(!is_null($startDate) && is_null($endDate), function ($q) use ($startDate) {
                        return $q->where('sales_orders.order_date', '>=', $startDate);
                    })
                    ->when(is_null($startDate) && !is_null($endDate), function ($q) use ($endDate) {
                        return $q->where('sales_orders.order_date', '<=', $endDate);
                    })
                    ->whereNull('sales_orders.deleted_at')
                    ->whereNull('sales_order_details.deleted_at')
                    ->unionAll(
                        // 入金伝票データ
                        DB::table('deposit_orders')
                            ->select(
                                'deposit_orders.order_number as transaction_number',
                                'deposit_orders.order_date as transaction_date',
                                DB::raw("'" . TransactionsTypeNameConst::TRANSACTION_TYPE_NAME_DEPOSIT . "' as transaction_type"),
                                'deposit_orders.transaction_type_id',
                                DB::raw('0 as amount'),
                                'deposit_orders.deposit as deposit_amount',
                                'deposit_orders.created_at',
                                DB::raw('NULL as product_id'),
                                DB::raw('NULL as product_name'),
                                DB::raw('NULL as quantity'),
                                DB::raw('NULL as unit_name'),
                                DB::raw('NULL as unit_price'),
                                DB::raw('NULL as sub_total'),
                                DB::raw('NULL as tax_amount'),
                                DB::raw('NULL as reduced_tax_flag'),
                                DB::raw('NULL as branch_name'),
                                'deposit_order_details.amount_cash',
                                'deposit_order_details.amount_check',
                                'deposit_order_details.amount_transfer',
                                'deposit_order_details.amount_bill',
                                'deposit_order_details.amount_offset',
                                'deposit_order_details.amount_discount',
                                'deposit_order_details.amount_fee',
                                'deposit_order_details.amount_other'
                            )
                            ->join('deposit_order_details', 'deposit_orders.id', '=', 'deposit_order_details.deposit_order_id')
                            ->where('deposit_orders.customer_id', $customer->id)
                            ->when(!is_null($startDate) && !is_null($endDate), function ($q) use ($startDate, $endDate) {
                                return $q->whereBetween('deposit_orders.order_date', [$startDate, $endDate]);
                            })
                            ->when(!is_null($startDate) && is_null($endDate), function ($q) use ($startDate) {
                                return $q->where('deposit_orders.order_date', '>=', $startDate);
                            })
                            ->when(is_null($startDate) && !is_null($endDate), function ($q) use ($endDate) {
                                return $q->where('deposit_orders.order_date', '<=', $endDate);
                            })
                            ->whereNull('deposit_orders.deleted_at')
                            ->whereNull('deposit_order_details.deleted_at')
                    );
            }, 'ledger_data')
                ->orderBy('ledger_data.transaction_date')
                ->orderBy('ledger_data.created_at')
                ->get();

            // 税率ごとの売上合計を計算
            $taxTotals = [
                'tax8' => $ledgerData->where('reduced_tax_flag', ReducedTaxFlagConst::REDUCED_TAX_FLAG_REDUCED)->sum('sub_total'),
                'tax10' => $ledgerData->where('reduced_tax_flag', ReducedTaxFlagConst::REDUCED_TAX_FLAG_NOMAL)->sum('sub_total'),
            ];

            // 担当者名を取得
            $employeeName = $customer->employee->name;

            // 得意先情報を追加
            $customersEmployeeData[] = [
                'customer' => [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'tel_number' => $customer->tel_number,
                ],
                'employee_name' => $employeeName,
                'ledger_data' => $ledgerData->toArray(),
                'sales_total' => $ledgerData->sum('sub_total'),
                'tax_total' => $ledgerData->sum('tax_amount'),
                'deposit_total' => $ledgerData->sum('deposit_amount'),
                'balance_total' => $chargeTotalAmount + $ledgerData->sum('sub_total') - $ledgerData->sum('deposit_amount'),
                'charge_total' => $chargeTotalAmount,
                'tax_totals' => $taxTotals,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ];
        }

        return $customersEmployeeData;
    }
}
