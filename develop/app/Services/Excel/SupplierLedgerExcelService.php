<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Consts\ReportOutput\ReducedTaxFlagConst;
use App\Consts\ReportOutput\TransactionsTypeNameConst;
use App\Enums\PaymentMethodType;
use App\Enums\TransactionType;
use App\Models\Invoice\ChargeData;
use App\Models\Master\MasterSupplier;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupplierLedgerExcelService extends AbstractExcelService
{
    /** 繰越残高の行位置 */
    protected int $balance_row_detail = 10;

    /** 明細行の開始位置 */
    protected int $start_row_detail = 11;

    /** 明細行の終了位置 */
    protected int $end_row_detail = 32;

    /** 仕入合計の行位置 */
    protected int $purchase_row_detail = 34;

    /** 消費税合計の行位置 */
    protected int $tax_row_detail = 35;

    /** 支払合計の行位置 */
    protected int $payment_row_detail = 37;

    /** 1ページの最大明細行数・残高の行位置 */
    protected int $max_row_detail = 38;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.supplier_ledger')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.supplier_ledger')
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
        // テンプレートファイルの読み込み
        $activeSheet = $this->initSpreadSheet(storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.supplier_ledger')
        ), PageSetup::ORIENTATION_LANDSCAPE);

        // ヘッダー情報の設定
        $this->setHeader($activeSheet, $outputData);

        // 繰越残高の出力
        $this->setCarryOver($activeSheet, $outputData['carry_over']);

        // 明細行の出力
        $this->setDetails($activeSheet, $outputData['ledger_data']);

        // 仕入合計行の出力
        $this->setPurchaseTotalRow($activeSheet, $outputData['purchase_total'], $outputData['tax_total']);

        // 消費税合計行の出力
        $this->setTaxes($activeSheet, $outputData['tax_totals']);

        // 支払合計行の出力
        $this->setPaymentTotalRow($activeSheet, $outputData['payment_total']);

        // 残高行を出力
        $this->setBalanceRow($activeSheet, $outputData['balance_total']);

        return $this->spreadSheet;
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
        // 指定した位置に行を挿入
        $sheet->insertNewRowBefore($startRow, $addRows);

        // 挿入された行を2行1組として処理
        for ($i = 0; $i < $addRows; ++$i) {
            $targetRow = $startRow + $i;
            $this->setFormatCells($sheet, $targetRow);

            // 最初の行だけ上下罫線を消す処理
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$targetRow}:S{$targetRow}")->getBorders()->getTop()->setBorderStyle(
                    Border::BORDER_NONE
                );
                $sheet->getStyle("A{$targetRow}:S{$targetRow}")->getBorders()->getBottom()->setBorderStyle(
                    Border::BORDER_NONE
                );
            }
        }

        // 行を挿入したことによる行番号の調整
        if ($startRow <= $this->end_row_detail) {
            $this->end_row_detail += $addRows;
        }
        if ($startRow <= $this->purchase_row_detail) {
            $this->purchase_row_detail += $addRows;
        }
        if ($startRow <= $this->tax_row_detail) {
            $this->tax_row_detail += $addRows;
        }
        if ($startRow <= $this->payment_row_detail) {
            $this->payment_row_detail += $addRows;
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
        // C～H列を結合
        $sheet->mergeCells("C{$row}:H{$row}");

        $sheet->getStyle("C{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
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
        $supplier = $outputData['supplier'];
        $period = $outputData['period'];

        // 日付範囲をフォーマット
        $dateRange = $this->formatDateRange($period['start_date'] ?? null, $period['end_date'] ?? null);

        // ヘッダー情報を設定
        $sheet->setCellValue('I2', $supplier['code'] . ' : ' . $supplier['name']);
        $sheet->getStyle('I2')->getFont()->setSize(18);
        $sheet->setCellValue('S3', Carbon::now()->format('Y/m/d'));
        $sheet->setCellValue('B3', $supplier['address']);
        $sheet->setCellValue('B4', $supplier['tel_number']);
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
        $sheet->setCellValue('C' . $row, '【前月　残高】');
        $sheet->setCellValue('O' . $row, $carryOver);
        $this->setNumberFormat($sheet, 'O', $row, $row);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
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

        // 仕入伝票番号と支払伝票番号ごとにデータをグループ化
        $groupedData = collect($ledgerData)->groupBy(function ($item) {
            return $item->transaction_type . '_' . $item->transaction_number;
        });

        // 必要総行数の計算
        $totalRows = 0;
        foreach ($groupedData as $group) {
            $paymentMethods = 0;
            foreach ($group as $data) {
                if ($data->transaction_type !== TransactionsTypeNameConst::TRANSACTION_TYPE_NAME_PAYMENT) {
                    continue;
                }
                foreach (PaymentMethodType::asSelectArray() as $method => $methodName) {
                    $methodType = PaymentMethodType::getPaymentType($method);
                    $colName = 'amount_' . $methodType;
                    if (property_exists($data, $colName) && $data->$colName > 0) {
                        ++$paymentMethods;
                    }
                }
            }

            // 支払方法ごとの必要行数を計算
            $groupRows = count($group) * 2;  // 各データの行
            $groupRows += ($paymentMethods > 0) ? ($paymentMethods - count($group)) * 2 : 0; // 支払方法の行
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
                $runningBalance += $data->sub_total - $data->payment_amount;

                // 税率ごとの合計を計算
                if ($data->reduced_tax_flag == ReducedTaxFlagConst::REDUCED_TAX_FLAG_REDUCED) {
                    $groupTax8 += $data->sub_total;
                }
                if ($data->reduced_tax_flag == ReducedTaxFlagConst::REDUCED_TAX_FLAG_NOMAL) {
                    $groupTax10 += $data->sub_total;
                }

                // 支払と仕入で処理を分岐
                if ($data->transaction_type === TransactionsTypeNameConst::TRANSACTION_TYPE_NAME_PAYMENT) {
                    $this->setPayment($sheet, $data, $row);
                }
                if ($data->transaction_type === TransactionsTypeNameConst::TRANSACTION_TYPE_NAME_PURCHASE) {
                    $this->setPurchase($sheet, $data, $row);
                }
            }

            // 【伝票計】8%の行を挿入
            $this->setOutputRow($sheet, $row, [
                'C' => '【伝　票　計】',
                'I' => '8%',
                'L' => $groupTax8 > 0 ? $groupTax8 : '',
                'O' => $runningBalance,
            ]);
            $this->setNumberFormat($sheet, 'L', $row, $row);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            ++$row;

            // 【伝票計】10%の行を挿入
            $this->setOutputRow($sheet, $row, [
                'C' => '【伝　票　計】',
                'I' => '10%',
                'L' => $groupTax10 > 0 ? $groupTax10 : '',
                'O' => $runningBalance,
            ]);
            $this->setNumberFormat($sheet, 'L', $row, $row);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            ++$row;
        }
    }

    /**
     * 明細の支払行を出力
     *
     * @param Worksheet $sheet
     * @param object $data 明細データ
     * @param int &$row 現在の行番号（参照渡し）
     * @return void
     */
    private function setPayment(Worksheet $sheet, $data, int &$row): void
    {
        foreach (PaymentMethodType::asSelectArray() as $method => $methodName) {
            $methodType = PaymentMethodType::getPaymentType($method);
            $colName = 'amount_' . $methodType;

            // 除外条件
            if (!property_exists($data, $colName) || $data->$colName <= 0) {
                continue;
            }

            // 伝票日付と商品コードの行を表示
            $this->setOutputRow($sheet, $row, [
                'A' => Carbon::parse($data->transaction_date)->format('Y/m/d'),
                'C' => $data->product_id ?? '',
            ]);

            // セルの結合と書式設定
            $this->setFormatCells($sheet, $row);
            ++$row;

            // 取引種別名を取得
            $transactionTypeName = TransactionType::asSelectArray()[$data->transaction_type_id] ?? '';

            // 支払方法ごとに行を出力
            $this->setOutputRow($sheet, $row, [
                'A' => $data->transaction_number ?? '',
                'B' => $transactionTypeName,
                'C' => $methodName, // 支払方法名
                'N' => $data->$colName, // 支払金額
            ]);

            $this->setNumberFormat($sheet, 'O', $row, $row);
            ++$row;
        }
    }

    /**
     * 明細の仕入行を出力
     *
     * @param Worksheet $sheet
     * @param object $data 明細データ
     * @param int &$row 現在の行番号（参照渡し）
     * @return void
     */
    private function setPurchase(Worksheet $sheet, $data, int &$row): void
    {
        // 伝票日付と商品コードの行を表示
        $this->setOutputRow($sheet, $row, [
            'A' => Carbon::parse($data->transaction_date)->format('Y/m/d'),
            'C' => $data->product_id ?? '',
        ]);

        // セルの結合と書式設定
        $this->setFormatCells($sheet, $row);
        ++$row;

        // 取引種別名を取得
        $transactionTypeName = TransactionType::asSelectArray()[$data->transaction_type_id] ?? '';

        // 仕入データを出力
        $this->setOutputRow($sheet, $row, [
            'A' => $data->transaction_number ?? '',
            'B' => $transactionTypeName,
            'C' => $data->product_name ?? '',
            'I' => $data->quantity ?? '',
            'J' => $data->unit_name ?? '',
            'K' => $data->unit_price ?? '',
            'L' => $data->sub_total ?? '',
        ]);
        $this->setNumberFormat($sheet, 'L', $row, $row);
        ++$row;
    }

    /**
     * 仕入合計行を出力
     *
     * @param Worksheet $sheet
     * @param int $purchaseTotal 仕入合計データ
     * @param int $taxTotal 消費税合計データ
     * @return void
     */
    private function setPurchaseTotalRow(Worksheet $sheet, int $purchaseTotal, int $taxTotal): void
    {
        $row = $this->purchase_row_detail;
        $sheet->setCellValue('C' . $row, '【仕入　合計】');
        $sheet->setCellValue('L' . $row, $purchaseTotal);
        $sheet->setCellValue('M' . $row, $taxTotal);
        $this->setNumberFormat($sheet, 'L', $row, $row);
        $this->setNumberFormat($sheet, 'M', $row, $row);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * 消費税合計行を出力
     *
     * @param Worksheet $sheet
     * @param array $taxTotals 税率ごとの仕入合計・消費税合計データ
     * @return void
     */
    private function setTaxes(Worksheet $sheet, array $taxTotals): void
    {
        $row = $this->tax_row_detail;

        // 税率8%の仕入合計と消費税合計を出力
        $sheet->setCellValue('C' . $row, '【仕入　合計】');
        $sheet->setCellValue('I' . $row, '8%');
        $sheet->setCellValue('L' . $row, $taxTotals['tax8'] ?? 0);
        $sheet->setCellValue('M' . $row, ($taxTotals['tax8'] ?? 0) * 0.08);
        $this->setNumberFormat($sheet, 'L', $row, $row);
        $this->setNumberFormat($sheet, 'M', $row, $row);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        ++$row;

        // 税率10%の仕入合計と消費税合計を出力
        $sheet->setCellValue('C' . $row, '【仕入　合計】');
        $sheet->setCellValue('I' . $row, '10%');
        $sheet->setCellValue('L' . $row, $taxTotals['tax10'] ?? 0);
        $sheet->setCellValue('M' . $row, ($taxTotals['tax10'] ?? 0) * 0.10);
        $this->setNumberFormat($sheet, 'L', $row, $row);
        $this->setNumberFormat($sheet, 'M', $row, $row);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * 支払合計行を出力
     *
     * @param Worksheet $sheet
     * @param int $paymentTotal 支払
     * @return void
     */
    private function setPaymentTotalRow(Worksheet $sheet, int $paymentTotal): void
    {
        $row = $this->payment_row_detail;
        $sheet->setCellValue('C' . $row, '【支払　合計】');
        $sheet->setCellValue('N' . $row, $paymentTotal);
        $this->setNumberFormat($sheet, 'N', $row, $row);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
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
        $sheet->setCellValue('C' . $row, '【当月　残高】');
        $sheet->setCellValue('O' . $row, $balanceTotal);
        $this->setNumberFormat($sheet, 'O', $row, $row);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
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
        // 仕入先情報を取得
        $supplier = MasterSupplier::find($searchConditions['supplier_id']);

        if (is_null($supplier)) {
            return [];
        }

        // 出力期間を設定
        $orderDate = $searchConditions['order_date'] ?? [];
        $startDate = $orderDate['start'] ?? null;
        $endDate = $orderDate['end'] ?? null;

        // 繰越残高を取得
        $carryOver = ChargeData::getPreviousMonthCarryOver($searchConditions);
        $carryOverAmount = $carryOver->carryOver ?? 0;

        // 仕入データと支払データを結合して取得
        $ledgerData = DB::table(function ($query) use ($supplier, $startDate, $endDate) {
            // 仕入伝票データ
            $query->select(
                'purchase_orders.order_number as transaction_number',
                'purchase_orders.order_date as transaction_date',
                DB::raw("'" . TransactionsTypeNameConst::TRANSACTION_TYPE_NAME_PURCHASE . "' as transaction_type"),
                'purchase_orders.transaction_type_id',
                'purchase_orders.purchase_total as amount',
                DB::raw('0 as payment_amount'),
                'purchase_orders.created_at',
                'purchase_order_details.product_id',
                'purchase_order_details.product_name',
                'purchase_order_details.quantity',
                'purchase_order_details.unit_name',
                'purchase_order_details.unit_price',
                'purchase_order_details.sub_total',
                'purchase_order_details.sub_total_tax as tax_amount',
                'purchase_order_details.reduced_tax_flag',
                DB::raw('NULL as amount_cash'),
                DB::raw('NULL as amount_check'),
                DB::raw('NULL as amount_transfer'),
                DB::raw('NULL as amount_bill'),
                DB::raw('NULL as amount_offset'),
                DB::raw('NULL as amount_discount'),
                DB::raw('NULL as amount_fee'),
                DB::raw('NULL as amount_other')
            )
                ->from('purchase_orders')
                ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.purchase_order_id')
                ->where('purchase_orders.supplier_id', $supplier->id)
                ->when(!is_null($startDate) && !is_null($endDate), function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('purchase_orders.order_date', [$startDate, $endDate]);
                })
                ->when(!is_null($startDate) && is_null($endDate), function ($q) use ($startDate) {
                    return $q->where('purchase_orders.order_date', '>=', $startDate);
                })
                ->when(is_null($startDate) && !is_null($endDate), function ($q) use ($endDate) {
                    return $q->where('purchase_orders.order_date', '<=', $endDate);
                })
                ->whereNull('purchase_orders.deleted_at')
                ->whereNull('purchase_order_details.deleted_at')
                ->unionAll(
                    // 支払伝票データ
                    DB::table('payments')
                        ->select(
                            'payments.order_number as transaction_number',
                            'payments.order_date as transaction_date',
                            DB::raw("'" . TransactionsTypeNameConst::TRANSACTION_TYPE_NAME_PAYMENT . "' as transaction_type"),
                            'payments.transaction_type_id',
                            DB::raw('0 as amount'),
                            'payments.payment as payment_amount',
                            'payments.created_at',
                            DB::raw('NULL as product_id'),
                            DB::raw('NULL as product_name'),
                            DB::raw('NULL as quantity'),
                            DB::raw('NULL as unit_name'),
                            DB::raw('NULL as unit_price'),
                            DB::raw('NULL as sub_total'),
                            DB::raw('NULL as tax_amount'),
                            DB::raw('NULL as reduced_tax_flag'),
                            'payment_details.amount_cash',
                            'payment_details.amount_check',
                            'payment_details.amount_transfer',
                            'payment_details.amount_bill',
                            'payment_details.amount_offset',
                            'payment_details.amount_discount',
                            'payment_details.amount_fee',
                            'payment_details.amount_other'
                        )
                        ->join('payment_details', 'payments.id', '=', 'payment_details.payment_id')
                        ->where('payments.supplier_id', $supplier->id)
                        ->when(!is_null($startDate) && !is_null($endDate), function ($q) use ($startDate, $endDate) {
                            return $q->whereBetween('payments.order_date', [$startDate, $endDate]);
                        })
                        ->when(!is_null($startDate) && is_null($endDate), function ($q) use ($startDate) {
                            return $q->where('payments.order_date', '>=', $startDate);
                        })
                        ->when(is_null($startDate) && !is_null($endDate), function ($q) use ($endDate) {
                            return $q->where('payments.order_date', '<=', $endDate);
                        })
                        ->whereNull('payments.deleted_at')
                        ->whereNull('payment_details.deleted_at')
                );
        }, 'ledger_data')
            ->orderBy('ledger_data.transaction_date')
            ->orderBy('ledger_data.created_at')
            ->get();

        // 税率ごとの支払合計を計算
        $taxTotals = [
            'tax8' => $ledgerData->where('reduced_tax_flag', 1)->sum('sub_total'),
            'tax10' => $ledgerData->where('reduced_tax_flag', 0)->sum('sub_total'),
        ];

        // 仕入先情報を追加
        return [
            'supplier' => [
                'id' => $supplier->id,
                'code' => $supplier->code,
                'name' => $supplier->name,
                'address' => $supplier->address,
                'tel_number' => $supplier->tel_number,
            ],
            'ledger_data' => $ledgerData->toArray(),
            'purchase_total' => $ledgerData->sum('sub_total'),
            'tax_total' => $ledgerData->sum('tax_amount'),
            'payment_total' => $ledgerData->sum('payment_amount'),
            'balance_total' => $carryOverAmount + $ledgerData->sum('sub_total') - $ledgerData->sum('payment_amount'),
            'carry_over' => $carryOverAmount,
            'tax_totals' => $taxTotals,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }
}
