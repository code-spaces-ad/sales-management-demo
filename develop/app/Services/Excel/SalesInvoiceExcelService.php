<?php

namespace App\Services\Excel;

use App\Consts\DB\Master\MasterCustomersConst;
use App\Enums\OrderType;
use App\Enums\ReducedTaxFlagType;
use App\Http\Requests\Invoice\InvoicePrintRequest;
use App\Models\Invoice\ChargeData;
use App\Models\Invoice\ChargeDetail;
use App\Models\Master\MasterHeadOfficeInfo;
use App\Models\Sale\SalesOrder;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesInvoiceExcelService
{
    /**
     * Excelデータ作成
     *
     * @param Spreadsheet $spreadsheet
     * @param InvoicePrintRequest $request
     * @param int $output_type 1 = "Excel" / 2 = "PDF"
     * @param int $charge_data_id
     * @return Spreadsheet
     *
     * @throws Exception
     */
    public function getSpreadSheet(Spreadsheet $spreadsheet,
        InvoicePrintRequest $request,
        int $output_type,
        int $charge_data_id): Spreadsheet
    {
        // 発行日
        $issue_date = explode(',', $request->input('issue_date'));
        // 会社情報
        $office_info = MasterHeadOfficeInfo::fixedOnly()->get();

        // 出力請求データ取得
        $charge_data = ChargeData::where('id', $charge_data_id)->get();
        $sales_invoice_printing_method = 0;

        // 請求書作成(請求データ毎のループ）※元々複数請求データ処理だったため
        foreach ($charge_data as $key => $data) {
            // ヘッダ頁：編集対象のシート取得
            $active_sheet_h = $this->getActiveSheet(
                $spreadsheet,
                $spreadsheet->getSheetbyName('template_a'),
                $data
            );

            // ヘッダ頁：ヘッダ部スタイル設定
            $this->setStyleHeaderPageTopRow($active_sheet_h, $output_type);

            // ヘッダ頁：共通データ設定
            $this->setDataHeaderPageTopRow($active_sheet_h, $office_info, $data, $issue_date[0]);

            // 明細行
            $order_details = ChargeDetail::getOrderDetail($data->id);
            if (count($order_details) === 0) {
                $current_row = 60;
                break;
            }

            $current_row = 31;    // 現在行
            // ヘッダ頁：列タイトル行
            $this->setStyleColumnTitleRow($active_sheet_h, $current_row, $current_row, 1);
            ++$current_row;

            // ヘッダ頁：明細部
            $back_color_reverse = -1;
            foreach ($order_details as $order_detail) {
                if ($order_detail->order_type === OrderType::DEPOSIT) {
                    $arrDeposit = SalesInvoiceCommonService::getDepositPaymentDetail1($order_detail);
                    $deposit_detail_count = 0;
                    for ($i = 0; $i < count($arrDeposit); ++$i) {
                        if ($arrDeposit[$i]['amount'] != 0) {
                            ++$deposit_detail_count;
                            $this->setStyleDetailRow($active_sheet_h, $current_row, $back_color_reverse, '0');
                            $back_color_reverse *= -1;
                            $this->setConstrSiteSummaryDataDepositDetailRow(
                                $active_sheet_h, $current_row, $order_detail,
                                $deposit_detail_count, $i,
                                $arrDeposit[$i]['amount'], $arrDeposit[$i]['note'],
                                $arrDeposit[$i]['date'], $arrDeposit[$i]['number']);
                            ++$current_row;
                        }
                    }
                } else {
                    // 文字色設定
                    $fore_color_type = '0';
                    if ($order_detail->consumption_tax_rate == 0) {
                        $fore_color_type = '2';
                    } else {
                        if ($order_detail->tax_type_id == 2) {
                            $fore_color_type = '1';
                        }
                    }
                    $this->setStyleDetailRow($active_sheet_h, $current_row, $back_color_reverse, $fore_color_type);
                    $back_color_reverse *= -1;
                    // 明細行
                    $this->setConstrSiteDetailDataDetailRow($active_sheet_h, $current_row++, $order_detail);
                }
            }
            // ヘッダ頁：税額サマリー行（６行）
            $this->setStyleDetailRow($active_sheet_h, $current_row, $back_color_reverse, '0');
            $back_color_reverse *= -1;
            ++$current_row;
            for ($i = $current_row; $i <= $current_row; ++$i) {
                $ret_rows = $this->setConstrSiteSummaryTaxDetailRow($active_sheet_h, $i, $data, $back_color_reverse);
            }
            $current_row = $i + $ret_rows;

        }
        // 印刷範囲の設定
        $this->setPagePrintArea($active_sheet_h, $current_row, $sales_invoice_printing_method);

        // 先頭のシートをアクティブにする
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * 編集対象のシート取得
     *
     * @param Spreadsheet $spreadsheet
     * @param Worksheet $sheet
     * @param ChargeData $data
     * @return Worksheet
     */
    private function getActiveSheet(Spreadsheet $spreadsheet,
        Worksheet $sheet,
        ChargeData $data): Worksheet
    {
        // シート名
        $customer_id = $data->customer_id;
        $sheet_name =
            str_pad($customer_id, MasterCustomersConst::CODE_MAX_LENGTH, '0', STR_PAD_LEFT);
        $sheet->setTitle($sheet_name);

        // 編集対象のシート取得
        return $spreadsheet->getSheetByName($sheet_name);
    }

    /**
     * @param Worksheet $sheet
     * @param int $current_row
     * @param int $sales_invoice_printing_method
     * @return void
     *
     * @throws Exception
     */
    private function setPagePrintArea(Worksheet $sheet,
        int $current_row,
        int $sales_invoice_printing_method): void
    {
        // 出力サイズ
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        // 出力方向
        $print_direction = $sales_invoice_printing_method == 1 ? PageSetup::ORIENTATION_LANDSCAPE : PageSetup::ORIENTATION_PORTRAIT;
        $sheet->getPageSetup()->setOrientation($print_direction);
        // 左右の中央揃え
        $sheet->getPageSetup()->setHorizontalCentered(true);
        // 余白
        $objPageMargins = $sheet->getPageMargins();
        $objPageMargins->setTop(0.5)->setRight(0.17)->setLeft(0.15)->setBottom(0.5)->setHeader(0.2);
        // 出力レイアウト
        $sheet->getPageSetup()->setPrintArea("A1:BQ$current_row");

        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
    }

    /**
     * ヘッダ頁・ヘッダ部のスタイル設定
     *
     * @param Worksheet $sheet
     * @param int $output_type
     * @return void
     */
    protected function setStyleHeaderPageTopRow(Worksheet $sheet,
        int $output_type): void
    {
        // PDF出力の場合、行高さ調整
        if ($output_type == 2) {
            for ($i = 1; $i <= 19; ++$i) {
                $sheet->getRowDimension($i)->setRowHeight(16.5);
            }
        }
    }

    /**
     * 列タイトル部のスタイル設定
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @param int $current_row2
     * @param int $change_back_color
     * @return void
     *
     * @throws Exception
     */
    protected function setStyleColumnTitleRow(Worksheet $sheet,
        int $current_row,
        int $current_row2,
        int $change_back_color): void
    {
        // 行の高さ
        for ($i = $current_row; $i <= $current_row2; ++$i) {
            $sheet->getRowDimension($i)->setRowHeight(30);
        }
        // セルのマージ
        $this->setStyleColumnDetailMerge($sheet, $current_row, $current_row2);
        // セルの横位置
        $this->setStyleColumnDetailTitleAlign($sheet, $current_row);
        // セルの罫線
        $this->setStyleColumnDetailLine($sheet, $current_row);
        // 背景色
        if ($change_back_color == 1) {
            $this->setStyleColumnDetailColor($sheet, $current_row,
                config('consts.default.sales_invoice_print.row_back_color_reverse'));
        }
        // 列見出し
        $this->setDataColumnTitle($sheet, $current_row);
    }

    /**
     * @param Worksheet $sheet
     * @param int $current_row
     * @return void
     */
    private function setDataColumnTitle(Worksheet $sheet,
        int $current_row): void
    {
        $sheet
            ->setCellValue("C$current_row", '日付')
            ->setCellValue("H$current_row", '伝票番号')
            ->setCellValue("M$current_row", '商　品　名')
            ->setCellValue("AE$current_row", '数量')
            ->setCellValue("AH$current_row", '単位')
            ->setCellValue("AK$current_row", '単価')
            ->setCellValue("AP$current_row", '売上金額')
            ->setCellValue("AW$current_row", '税区分')
            ->setCellValue("BA$current_row", '入金額')
            ->setCellValue("BG$current_row", '備考');
        $cell = "C$current_row:BO$current_row";
        $sheet->getStyle($cell)->getFont()->setSize(16)->getColor()->setARGB('FF000000');
    }

    /**
     * 明細部・行項目のマージ
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @param int $current_row2
     * @return void
     *
     * @throws Exception
     */
    protected function setStyleColumnDetailMerge(Worksheet $sheet,
        int $current_row,
        int $current_row2): void
    {
        // 結合・日付
        $cell01 = "C$current_row:G$current_row2";
        // 結合・伝票番号
        $cell02 = "H$current_row:L$current_row2";
        // 結合・商品
        $cell03 = "M$current_row:AD$current_row2";
        // 結合・数量
        $cell04 = "AE$current_row:AG$current_row2";
        // 結合・単位
        $cell05 = "AH$current_row:AJ$current_row2";
        // 結合・単価
        $cell06 = "AK$current_row:AO$current_row2";
        // 結合・金額
        $cell07 = "AP$current_row:AV$current_row2";
        // 結合・税区分
        $cell08 = "AW$current_row:AZ$current_row2";
        // 結合・入金額
        $cell09 = "BA$current_row:BF$current_row2";
        // 結合・備考
        $cell10 = "BG$current_row:BO$current_row2";
        // 結合・支所名
        $cell11 = "BP$current_row:BQ$current_row2";

        $sheet
            ->mergeCells($cell01)->mergeCells($cell02)->mergeCells($cell03)->mergeCells($cell04)->mergeCells($cell05)
            ->mergeCells($cell06)->mergeCells($cell07)->mergeCells($cell08)->mergeCells($cell09)->mergeCells($cell10)
            ->mergeCells($cell11);
    }

    /**
     * 明細部・行項目のマージ
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @return void
     */
    private function setStyleColumnDetailTitleAlign(Worksheet $sheet,
        int $current_row): void
    {
        // 結合・日付
        $cell_date = "C$current_row:G$current_row";
        // 結合・伝票番号
        $cell_order_number = "H$current_row:L$current_row";
        // 結合・商品
        $cell_product = "M$current_row:AD$current_row";
        // 結合・数量
        $cell_quantity = "AE$current_row:AG$current_row";
        // 結合・単位
        $cell_unit = "AH$current_row:AJ$current_row";
        // 結合・単価
        $cell_unit_price = "AK$current_row:AO$current_row";
        // 結合・金額
        $cell_sub_total = "AP$current_row:AV$current_row";
        // 結合・税区分
        $cell_tax_type = "AW$current_row:AZ$current_row";
        // 結合・入金額
        $cell_deposit_total = "BA$current_row:BF$current_row";
        // 結合・備考
        $cell_note = "BG$current_row:BO$current_row";
        // 結合・支所名
        $cell_branch = "BP$current_row:BQ$current_row";

        $sheet->getStyle($cell_date)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($cell_order_number)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($cell_product)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($cell_quantity)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($cell_unit)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($cell_unit_price)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($cell_sub_total)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($cell_tax_type)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($cell_deposit_total)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($cell_note)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($cell_branch)->getAlignment()->setHorizontal('center');
    }

    /**
     * 明細部・行項目の罫線
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @return void
     */
    protected function setStyleColumnDetailLine(Worksheet $sheet,
        int $current_row): void
    {
        $cell = "C$current_row:BO$current_row";
        $sheet->getStyle($cell)->applyFromArray(PrintExcelCommonService::$arrStyleAllHair);
    }

    /**
     * 明細部・背景色
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @param string $back_color
     * @return void
     */
    private function setStyleColumnDetailColor(Worksheet $sheet,
        int $current_row,
        string $back_color): void
    {
        // 背景色
        $cell = "C$current_row:BO$current_row";
        $sheet->getStyle($cell)->getFill()->setFillType('solid')->getStartColor()->setARGB($back_color);
    }

    /**
     * 明細部・行項目の横位置
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @return void
     */
    protected function setStyleColumnDetailDataAlign(Worksheet $sheet,
        int $current_row): void
    {
        // 結合・日付
        $cell = "C$current_row:G$current_row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        // 結合・伝票番号
        $cell = "H$current_row:L$current_row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        // 結合・商品
        $cell = "M$current_row:AD$current_row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        // 結合・数量
        $cell = "AE$current_row:AG$current_row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        // 結合・単位
        $cell = "AH$current_row:AJ$current_row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        // 結合・単価
        $cell = "AK$current_row:AO$current_row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        // 結合・金額
        $cell = "AP$current_row:AV$current_row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        // 結合・税区分
        $cell = "AW$current_row:AZ$current_row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        // 結合・入金額
        $cell = "BA$current_row:BF$current_row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        // 結合・備考
        $cell = "BG$current_row:BO$current_row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        // 結合・支所名
        $cell = "BP$current_row:BQ$current_row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
    }

    /**
     * データ明細部のスタイル設定
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @param int $back_color_reverse
     * @param string $fore_color_type "0":外税（黒）／"1":内税（緑）／"2":非課税（赤）
     * @return void
     *
     * @throws Exception
     */
    protected function setStyleDetailRow(Worksheet $sheet,
        int $current_row,
        int $back_color_reverse,
        string $fore_color_type): void
    {
        // セルの行幅
        $sheet->getRowDimension($current_row)->setRowHeight(40);
        // セルのフォントサイズ
        $sheet->getStyle($current_row)->getFont()->setSize(16);
        // セルのマージ
        $this->setStyleColumnDetailMerge($sheet, $current_row, $current_row);
        // セルの罫線
        $this->setStyleColumnDetailLine($sheet, $current_row);
        // セルの横位置
        $this->setStyleColumnDetailDataAlign($sheet, $current_row);
        // 背景色
        if ($back_color_reverse > 0) {
            $this->setStyleColumnDetailDataBackColor($sheet, $current_row,
                config('consts.default.sales_invoice_print.row_back_color_reverse'));
        }
        // 前景色
        if ($fore_color_type == '1') {    // 内税
            $fore_color = config('consts.default.sales_invoice_print.row_fore_color_tax_in');
            $this->setStyleColumnDetailDataForeColor($sheet, $current_row, $fore_color);
        }
        if ($fore_color_type == '2') {    // 非課税
            $fore_color = config('consts.default.sales_invoice_print.row_fore_color_tax_free');
            $this->setStyleColumnDetailDataForeColor($sheet, $current_row, $fore_color);
        }
    }

    /**
     * 明細部・背景色
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @param string $back_color
     * @return void
     */
    protected function setStyleColumnDetailDataBackColor(Worksheet $sheet,
        int $current_row,
        string $back_color): void
    {
        // 背景色
        $cell = "C$current_row:BO$current_row";
        $sheet->getStyle($cell)->getFill()->setFillType('solid')->getStartColor()->setARGB($back_color);
    }

    /**
     * 明細部・文字色
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @param string $fore_color
     * @return void
     */
    protected function setStyleColumnDetailDataForeColor(Worksheet $sheet,
        int $current_row,
        string $fore_color): void
    {
        // 背景色
        $cell = "M$current_row:BO$current_row";
        $sheet->getStyle($cell)->getFont()->getColor()->setARGB($fore_color);
    }

    /**
     * @param Worksheet $sheet
     * @param int $current_row
     * @return void
     */
    private function setChangePage(Worksheet $sheet,
        int $current_row): void
    {
        $sheet->setBreak("A$current_row", Worksheet::BREAK_ROW);
    }

    /**
     * ページ共通の設定値を設定
     *
     * @param Worksheet $sheet
     * @param Collection<MasterHeadOfficeInfo> $office_info
     * @param ChargeData $data
     * @param string $issue_date
     * @return void
     *
     * @throws Exception
     */
    protected function setDataHeaderPageTopRow(Worksheet $sheet,
        Collection $office_info,
        ChargeData $data,
        string $issue_date): void
    {
        // 納品先名
        $sheet = $this->setCustomerInfo($sheet, $data);

        // 発行日
        $sheet = $this->setIssueDate($sheet, $issue_date);

        // 会社情報
        $sheet = $this->setCompany($sheet, $office_info);

        // 合計行
        $this->setTotalData($sheet, $data);
    }

    /**
     * 得意先の設定
     *
     * @param Worksheet $sheet シート情報
     * @param ChargeData $charge_data 請求情報
     * @return Worksheet
     */
    private function setCustomerInfo(Worksheet $sheet, ChargeData $charge_data): Worksheet
    {
        // 納品先郵便番号の設定
        if (!empty($charge_data->customer_postal_code1) || !empty($charge_data->customer_postal_code2)) {
            $cell = 'D3';
            $sheet->setCellValue($cell,
                '〒' . $charge_data->customer_postal_code1 . '-' . $charge_data->customer_postal_code2);
            $sheet->getStyle($cell)->getAlignment()->setShrinkToFit(true);
        }

        // 納品先住所の設定
        if (!empty($charge_data->customer_address1) || !empty($charge_data->customer_address2)) {
            $cell = 'D6';
            $sheet->setCellValue($cell,
                $charge_data->customer_address1 . $charge_data->customer_address2);
            $sheet->getStyle($cell)->getAlignment()->setShrinkToFit(true);
        }

        // 納品先名の設定
        $cell = 'D10';
        $sheet->setCellValue($cell, $charge_data->cname_htitle);
        $sheet->getStyle($cell)->getAlignment()->setShrinkToFit(true);

        // 得意先コードの設定
        $cell = 'L14';
        $sheet->setCellValue($cell, $charge_data->customer_code_zerofill);
        $sheet->getStyle($cell)->getAlignment()->setShrinkToFit(true);

        return $sheet;
    }

    /**
     * 発行日の設定
     *
     * @param Worksheet $sheet シート情報
     * @param string $issue_date 発行日
     * @return Worksheet
     */
    private function setIssueDate(Worksheet $sheet, string $issue_date): Worksheet
    {
        // 発行日の設定
        $cell = 'BI2:Bo2';
        $dateNew = DateTime::createFromFormat('Y-m-d', $issue_date)->format('Y年m月d日');
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getAlignment()->setShrinkToFit(true);
        $sheet->getStyle($cell)->getFont()->setsize(32)->getColor()->setARGB('FF000000');
        $cell = 'BI2';
        $sheet->setCellValue($cell, $dateNew);

        return $sheet;
    }

    /**
     * 会社情報の設定
     *
     * @param Worksheet $sheet シート情報
     * @param Collection<MasterHeadOfficeInfo> $office_info 会社情報
     * @return Worksheet
     */
    private function setCompany(Worksheet $sheet, Collection $office_info): Worksheet
    {
        foreach ($office_info as $office) {
            // 社印・社判の画像取り出し
            $imageData = $office->company_seal_image;
            $imageFileName = $office->company_seal_image_file_name;
            if (!is_null($imageFileName) && !empty($imageFileName)) {
                // エクセルシート上に画像表示
                $drawing = new MemoryDrawing();
                $drawing->setName('company_seal_image');
                $drawing->setImageResource(imagecreatefromstring($imageData));
                $drawing->setCoordinates('AX4');
                $drawing->setHeight(100);
                $drawing->setWidth(360);
                $drawing->setWorksheet($sheet);
            }

            // 会社情報（右上）
            $sheet
//                ->setCellValue('AW5', $office->company_name)
                ->setCellValue('AW9', ' ' . $office->address1 . $office->address2)
                ->setCellValue('AW10', 'TEL: ' . $office->tel_number . '    FAX: ' . $office->fax_number)
                ->setCellValue('AW11', ' ﾌﾘｰﾀﾞｲﾔﾙ: ' . $office->tel_number2)
                ->setCellValue('AW12', '  登録番号: ' . $office->invoice_number)
                ->setCellValue('AW13', '  振込先: ' . $office->bank_account1)
                ->setCellValue('AW14', '                ' . $office->bank_account2)
                ->setCellValue('AW15', '                ' . $office->bank_account3)
                ->setCellValue('AW16', '                ' . $office->bank_account4);
            $sheet->mergeCells('AW9:BN9');
            $sheet->getStyle('AW9:BN9')->getFont()->setSize(16);
            $sheet->getRowDimension(9)->setRowHeight(19);
            $sheet->getStyle('AW4')->getAlignment()->setShrinkToFit(true);
            $sheet->getStyle('AW9')->getAlignment()->setShrinkToFit(true);
            $sheet->getStyle('AW12')->getAlignment()->setShrinkToFit(true);
            $sheet->getStyle('AW13')->getAlignment()->setShrinkToFit(true);
            $sheet->getStyle('AW14')->getAlignment()->setShrinkToFit(true)->setHorizontal('left');
        }

        return $sheet;
    }

    /**
     * 合計行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param ChargeData $charge_data 請求情報
     * @return void
     */
    private function setTotalData(Worksheet $sheet, ChargeData $charge_data): void
    {
        // 締め日
        $charge_end_date = date('Y年m月d日', strtotime($charge_data->charge_end_date));
        $charge_end_date = '(' . $charge_end_date . '締切分' . ')';
        // ヘッダ部・合計欄
        $sheet
            ->setCellValue('C26', $charge_data->before_charge_total)
            ->setCellValue('K26', $charge_data->payment_total)
            ->setCellValue('S26', $charge_data->adjust_amount)
            ->setCellValue('AA26', $charge_data->carryover)
            ->setCellValue('AJ26', $charge_data->sales_total)
            ->setCellValue('AR26', $charge_data->sales_tax_total)
            ->setCellValue('BB25', $charge_data->charge_total)
            ->setCellValue('AQ30', $charge_data->sales_total + $charge_data->sales_tax_total)
            ->setCellValue('BB30', $charge_end_date);
        $cell = 'BB23:BO23';
        $sheet->getStyle('BB25')->getFont()->setSize(36);
        $sheet->getStyle('BB25')->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle($cell)->getFill()->setFillType('solid')->getStartColor()->setARGB('FF868686');
        $cell = 'BB23';
        $sheet->setCellValue($cell, '今回ご請求額');
    }

    /**
     * 明細頁（請求一覧）～明細行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param int $current_row
     * @param SalesOrder $order_detail 伝票情報
     * @return void
     */
    protected function setConstrSiteDetailDataDetailRow(Worksheet $sheet,
        int $current_row,
        SalesOrder $order_detail): void
    {
        // 日付
        $date = new Carbon($order_detail->order_date);
        // 商品名
        $product_name = $order_detail->reduced_tax_flag == ReducedTaxFlagType::REDUCED_TAX ?
            $order_detail->product_name . ' ＊' : $order_detail->product_name;
        // 数値区切り指定
        $decimal_separator = '.';
        $thousands_separator = ',';
        // 数量
        $quantity = $order_detail->quantity == null ? '－' :
            number_format($order_detail->quantity,
                $order_detail->quantity_decimal_digit,
                $decimal_separator,
                $thousands_separator);
        // 単価
        $unit_price = $order_detail->unit_price == null ? '－' :
            number_format($order_detail->unit_price,
                $order_detail->unit_price_decimal_digit,
                $decimal_separator,
                $thousands_separator);
        // 金額
        $sub_total = $order_detail->sub_total == null ? '' : number_format($order_detail->sub_total);
        // 税区分
        $tax_type = '';
        $tax_detail = '';
        if ($order_detail->order_type == 2) {
            if ($order_detail->consumption_tax_rate == 0) {
                $tax_type = '非';
            } else {
                if ($order_detail->tax_type_id == 1) {
                    $tax_type = '外';
                } else {
                    $tax_type = '内';
                    $tax_detail = '(' . $order_detail->sub_total_tax . ')';
                }
            }
            $tax_type .= sprintf('%02d', $order_detail->consumption_tax_rate);
        }
        // 入金額
        $deposit_total = $order_detail->deposit_total == null ? '' : number_format($order_detail->deposit_total);

        $format = '#,##0';
        $decimal_digit = $order_detail->unit_price_decimal_digit;
        if ($decimal_digit == 1) {
            $format = '#,##0.0';
        }
        if ($decimal_digit == 2) {
            $format = '#,##0.#0';
        }
        if ($decimal_digit == 3) {
            $format = '#,##0.##0';
        }
        if ($decimal_digit == 4) {
            $format = '#,##0.###0';
        }
        $sheet->getStyle("AK$current_row")->getNumberFormat()->setFormatCode($format);
        // 値のセット
        $sheet
            ->setCellValue("C$current_row", $date->format('Y/m/d'))
            ->setCellValue("H$current_row", $order_detail->order_number)
            ->setCellValue("M$current_row", $product_name)
            ->setCellValueExplicit("AE$current_row", $quantity, DataType::TYPE_STRING)
            ->setCellValue("AH$current_row", $order_detail->unit_name)
            ->setCellValueExplicit("AK$current_row", $unit_price, DataType::TYPE_STRING)
            ->setCellValue("AP$current_row", $sub_total)
            ->setCellValue("AW$current_row", $tax_type . $tax_detail)
            ->setCellValue("BA$current_row", $deposit_total)
            ->setCellValue("BG$current_row", $order_detail->detail_note)
            ->setCellValue("BP$current_row", $order_detail->mnemonic_name);
        $cell = 'C' . $current_row . ':' . 'BP' . $current_row;
        $sheet->getStyle($cell)->getAlignment()->setShrinkToFit(true);
    }

    /**
     * 明細頁（工事現場別一覧）～"入金"明細行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param int $current_row
     * @param ?SalesOrder $constr_site_order 伝票情報
     * @param int $detail_no
     * @param int $payment_type
     * @param int $amount
     * @param ?string $note
     * @param string|null $bill_date
     * @param string|null $bill_number
     * @return void
     */
    protected function setConstrSiteSummaryDataDepositDetailRow(
        Worksheet $sheet, int $current_row,
        ?SalesOrder $constr_site_order, int $detail_no, int $payment_type,
        int $amount, ?string $note,
        ?string $bill_date, ?string $bill_number): void
    {
        // 伝票日付（入金のみ出力）
        $date = new Carbon($constr_site_order->order_date);
        $order_date = $date->format('Y/m/d');
        // 伝票番号（入金のみ出力）
        $order_number = $constr_site_order->order_number;
        // 手形期日・手形番号
        $bill_date = new Carbon($bill_date);
        $bill_comment = '[' . '期日：' . $bill_date->format('Y/m/d') . ']' . '[' . '番号：' . $bill_number . ']';
        // 支払種類名
        $payment_name = '';
        if ($payment_type == 0) {
            $payment_name = '現金';
        }
        if ($payment_type == 1) {
            $payment_name = '小切手';
        }
        if ($payment_type == 2) {
            $payment_name = '振込';
        }
        if ($payment_type == 3) {
            $payment_name = '手形' . $bill_comment;
        }
        if ($payment_type == 4) {
            $payment_name = '相殺';
        }
        if ($payment_type == 5) {
            $payment_name = '値引';
        }
        if ($payment_type == 6) {
            $payment_name = '手数料';
        }
        if ($payment_type == 7) {
            $payment_name = 'その他';
        }
        // 値の設定
        $sheet
            ->setCellValue("C$current_row", $order_date)
            ->setCellValue("H$current_row", $order_number)
            ->setCellValue("M$current_row", $payment_name)
            ->setCellValue("AE$current_row", '－')
            ->setCellValue("AH$current_row", '－')
            ->setCellValue("AJ$current_row", '－')
            ->setCellValue("AO$current_row", '')
            ->setCellValue("AU$current_row", '')
            ->setCellValue("BA$current_row", number_format($amount))
            ->setCellValue("BG$current_row", $note);
        $cell = 'C' . $current_row . ':' . 'BO' . $current_row;
        $sheet->getStyle($cell)->getAlignment()->setShrinkToFit(true);
    }

    /**
     * 明細頁（工事現場別一覧）～税額サマリー行
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @param ChargeData|null $charge_data
     * @param int $back_color_reverse
     * @return int
     *
     * @throws Exception
     */
    protected function setConstrSiteSummaryTaxDetailRow(
        Worksheet $sheet, int $current_row, ?ChargeData $charge_data, int $back_color_reverse): int
    {

        $row = $current_row;

        $sales_total_normal_out = number_format($charge_data->sales_total_normal_out);
        $sales_total_reduced_out = number_format($charge_data->sales_total_reduced_out);
        $sales_total_normal_in = number_format($charge_data->sales_total_normal_in);
        $sales_total_reduced_in = number_format($charge_data->sales_total_reduced_in);
        $sales_total_free = number_format($charge_data->sales_total_free);
        $sales_tax_normal_out = number_format($charge_data->sales_tax_normal_out);
        $sales_tax_reduced_out = number_format($charge_data->sales_tax_reduced_out);
        $sales_tax_normal_in = number_format($charge_data->sales_tax_normal_in);
        $sales_tax_reduced_in = number_format($charge_data->sales_tax_reduced_in);

        // 税額サマリー行(固定で６行出力or金額アリの行だけ出力）
        $tax_summary_format = config('consts.default.sales_invoice_print.tax_summary_format');

        $tax_title[] = "【税別御買上計10% ￥$sales_total_normal_out 消費税10% ￥{$sales_tax_normal_out}】";
        $fore_color[] = config('consts.default.sales_invoice_print.row_fore_color_normal');
        $amount_zero_flg[] = ($sales_total_normal_out != '0' || $sales_tax_normal_out != '0') ? 0 : 1;

        $tax_title[] = "【税別御買上計軽減8% ￥$sales_total_reduced_out 消費税8% ￥{$sales_tax_reduced_out}】";
        $fore_color[] = config('consts.default.sales_invoice_print.row_fore_color_normal');
        $amount_zero_flg[] = ($sales_total_reduced_out != '0' || $sales_tax_reduced_out != '0') ? 0 : 1;

        $tax_title[] = "【税込御買上計10% ￥$sales_total_normal_in (内消費税10% ￥$sales_tax_normal_in)】";
        $fore_color[] = config('consts.default.sales_invoice_print.row_fore_color_tax_in');
        $amount_zero_flg[] = ($sales_total_normal_in != '0' || $sales_tax_normal_in != '0') ? 0 : 1;

        $tax_title[] = "【税込御買上計軽減8% ￥$sales_total_reduced_in (内消費税8% ￥$sales_tax_reduced_in)】";
        $fore_color[] = config('consts.default.sales_invoice_print.row_fore_color_tax_in');
        $amount_zero_flg[] = ($sales_total_reduced_in != '0' || $sales_tax_reduced_in != '0') ? 0 : 1;

        $tax_title[] = '注）税込御買上分　請求毎税は除く';
        $fore_color[] = config('consts.default.sales_invoice_print.row_fore_color_normal');
        $amount_zero_flg[] =
            ($sales_total_normal_in != '0' || $sales_tax_normal_in != '0' || $sales_total_reduced_in != '0' || $sales_tax_reduced_in != '0') ? 0 : 1;

        $tax_title[] = "【非課税 ￥{$sales_total_free}】";
        $fore_color[] = config('consts.default.sales_invoice_print.row_fore_color_tax_free');
        $amount_zero_flg[] = ($sales_total_free != '0') ? 0 : 1;

        $tax_row_count = 0;
        for ($i = 0; $i < count($tax_title); ++$i) {
            // 書式が0:固定６行を出力　又は、金額が０では無い場合
            if ($tax_summary_format === '0' || $amount_zero_flg[$i] === 0) {
                // 書式
                $this->setStyleDetailRow($sheet, $row, $back_color_reverse, '0');
                $back_color_reverse *= -1;
                // Alignment
                $sheet->getStyle("M$row:M$row")->getAlignment()->setHorizontal('right');
                // 値
                $sheet->setCellValue("M$row", $tax_title[$i]);
                $this->setStyleColumnDetailDataForeColor($sheet, $row, $fore_color[$i]);
                $cell = 'C' . $row . ':' . 'BO' . $row;
                $sheet->getStyle($cell)->getAlignment()->setShrinkToFit(true);
                ++$row; //
                ++$tax_row_count; // 返却用の税額行数
            }
        }

        return $tax_row_count;
    }
}
