<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Consts\DB\Trading\PaymentConst;
use App\Models\Trading\Payment;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class DepositSlipInquiryTransferFeeExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 8;

    /** 明細行の最大行 */
    protected int $max_row_detail = 18;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.deposit_slip_inquiry_transfer_fee')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.deposit_slip_inquiry_transfer_fee')
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
        $sheet = $this->initSpreadSheet(storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.deposit_slip_inquiry_transfer_fee')
        ), PageSetup::ORIENTATION_LANDSCAPE);

        // A4サイズ
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        // 横
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        // 左右の中央揃え
        $sheet->getPageSetup()->setHorizontalCentered(true);

        // ページ設定：拡大縮小印刷
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // タイトル
        $title = str_replace('**from_date', (new Carbon($searchConditions['start_date'])->format('Y/m/d')),
            '支払日[**from_date]-[**end_date] ＊＊ 支払伝票一覧 ＊＊ DATE : **output_date');
        $title = str_replace('**end_date', (new Carbon($searchConditions['end_date'])->format('Y/m/d')), $title);
        $title = str_replace('**output_date', (Carbon::today()->format('Y/m/d')), $title);
        $sheet->setCellValue('A1', '振込手数料明細　' . $title);

        $current_row = 3;
        //　データ部
        foreach ($outputData as $data) {
            // 支払日
            $sheet->setCellValue("A{$current_row}", new Carbon($data['order_date'])->format('Y/m/d'));
            // 伝票No
            $sheet->setCellValue("B{$current_row}",
                sprintf('%0' . PaymentConst::ORDER_NUMBER_MAX_LENGTH . 'd', $data['order_number']));
            // 支払先CD
            $sheet->setCellValueExplicit("C{$current_row}", $data['suppliers_code'], DataType::TYPE_STRING);
            // 支払先名
            $sheet->setCellValue("D{$current_row}", $data['suppliers_name']);
            // 支払金種
            $sheet->setCellValue("E{$current_row}", '6：手数料');
            // 支払金額
            $sheet->setCellValue("F{$current_row}", $data['amount_fee']);
            $sheet->getStyle("F{$current_row}")->getNumberFormat()->setFormatCode('#,##0');

            ++$current_row;
        }

        //　初期セル位置をA1にセット
        $sheet->setSelectedCell('A1');

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
        return Payment::query()
            ->select(
                'payments.order_date as order_date',
                'payments.order_number as order_number',
                'm_suppliers.code AS suppliers_code',
                'm_suppliers.name AS suppliers_name',
                'payment_details.amount_fee as amount_fee',
            )
            ->join('payment_details', 'payments.id', '=', 'payment_details.payment_id')
            ->join('m_suppliers', 'payments.supplier_id', '=', 'm_suppliers.id')
            // 伝票日付で絞り込み
            ->when($searchConditions !== null, function ($query) use ($searchConditions) {
                $start_date = $searchConditions['payment_date']['start'] ?? null;
                $end_date = $searchConditions['payment_date']['end'] ?? null;

                if (is_null($start_date) && is_null($end_date)) {
                    return $query;
                }
                if (isset($start_date) && is_null($end_date)) {
                    return $query->where('payments.order_date', '>=', $start_date);
                }
                if (is_null($start_date) && isset($end_date)) {
                    return $query->where('payments.order_date', '<=', $end_date);
                }

                return $query->whereBetween('payments.order_date', [$start_date, $end_date]);
            })
            ->where('amount_fee', '>', 0)
            ->orderBy('payments.order_date')
            ->orderBy('payments.order_number')
            ->get()
            ->toArray();
    }
}
