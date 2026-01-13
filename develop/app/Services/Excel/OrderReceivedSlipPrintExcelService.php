<?php

namespace App\Services\Excel;

use App\Models\Master\MasterHeadOfficeInfo;
use App\Models\Master\MasterHonorificTitle;
use App\Models\Receive\OrdersReceived;
use App\Models\Receive\OrdersReceivedDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment as Align;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderReceivedSlipPrintExcelService
{
    /** 各ブロックの開始位置 */
    protected $start_row_deliv_slip = 1;

    protected $start_row_deliv_slip_copy = 22;

    protected $start_row_deliv_receipt = 43;

    /**
     * Excelデータ作成
     *
     * @param Request $request
     * @return Spreadsheet
     *
     * @throws Exception
     */
    public function getSpreadSheet(Request $request): Spreadsheet
    {
        // 1ページの領域
        $max_deliv_slip_page_height = 59;
        // 1ブロックの最大明細行数 */
        $max_detail_row_per_block = 5;

        // テンプレートファイルの読み込み
        $path = storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.sale_delivery_slip_print_non_unit_name')
        );
        $spreadsheet = IOFactory::load($path);

        // シートの設定
        $deliv_slip_sheet = $spreadsheet->getActiveSheet();
        // 出力サイズ
        $deliv_slip_sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        // 出力方向
        $deliv_slip_sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        // 左右の中央揃え
        $deliv_slip_sheet->getPageSetup()->setHorizontalCentered(true);

        // ページ設定：拡大縮小印刷
        $deliv_slip_sheet->getPageSetup()->setPrintArea("A1:CA$max_deliv_slip_page_height");
        $deliv_slip_sheet->getPageSetup()->setFitToPage(true);
        $deliv_slip_sheet->getPageSetup()->setFitToWidth(1);
        $deliv_slip_sheet->getPageSetup()->setFitToHeight(0);

        // 会社情報
        $office_info = MasterHeadOfficeInfo::fixedOnly()->get();

        // 表示データ取得
        $search_result = OrdersReceived::query()->where('id', $request->id)->get();

        // 納品書作成
        foreach ($search_result as $data) {
            // ページ数
            $page_no = 1;

            // 編集対象のシート取得
            $active_sheet = $this->getActiveSheet(
                $spreadsheet,
                $deliv_slip_sheet,
                $page_no,
                $data
            );

            // ページ共通のデータを設定
            $this->setPublicPageData($active_sheet, $office_info, $data);

            // 明細行の行数
            $row_count = 0;

            foreach ($data->OrdersReceivedDetail as $key => $detail_data) {
                if (!isset($request->detail[$key]['delivery_print'])) {
                    continue;
                }
                if (!$request->detail[$key]['delivery_print']) {
                    continue;
                }
                // 年月日
                $active_sheet = $this->setDate($active_sheet, $detail_data);

                // １ページの最大明細行を超えた場合かつ、まだデータが存在する場合
                if (
                    $row_count >= $max_detail_row_per_block
                    && count($data->OrdersReceivedDetail) > ($max_detail_row_per_block * $page_no)
                ) {
                    ++$page_no;

                    // 編集対象のシート取得
                    $active_sheet = $this->getActiveSheet(
                        $spreadsheet,
                        $deliv_slip_sheet,
                        $page_no,
                        $data
                    );

                    // ページ共通のデータを設定
                    $active_sheet = $this->setPublicPageData($active_sheet, $office_info, $data);

                    $row_count = 0;
                }

                // 明細行
                $active_sheet = $this->setDetailData(
                    $active_sheet,
                    $row_count,
                    $detail_data
                );

                ++$row_count;
            }
        }

        if ($spreadsheet->getSheetCount() > 1) {
            // 先頭のシートを削除
            $spreadsheet->removeSheetByIndex(0);
        }

        // 先頭のシートをアクティブにする
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * 編集対象のシート取得
     *
     * @param Spreadsheet $spreadsheet
     * @param Worksheet $deliv_slip_sheet
     * @param int $page_no
     * @param $data
     * @return Worksheet
     *
     * @throws Exception
     */
    private function getActiveSheet(Spreadsheet $spreadsheet, Worksheet $deliv_slip_sheet, int $page_no, $data): Worksheet
    {
        // シートの複製
        $cloned_deliv_slip_sheet = clone $deliv_slip_sheet;
        // シート名
        $deliv_slip_sheet_name = '納品書_' . $data['id'] . '_' . $page_no;
        $cloned_deliv_slip_sheet->setTitle($deliv_slip_sheet_name);
        // シートの追加
        $spreadsheet->addSheet($cloned_deliv_slip_sheet);

        // 編集対象のシート取得
        return $spreadsheet->getSheetByName($deliv_slip_sheet_name);
    }

    /**
     * ページ共通の設定値を設定
     *
     * @param Worksheet $sheet
     * @param Collection $office_info
     * @param OrdersReceived $data
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setPublicPageData(Worksheet $sheet,
        Collection $office_info, OrdersReceived $data): Worksheet
    {
        // 納品先名
        $sheet = $this->setDelivName($sheet, $data);
        // 伝票番号
        $sheet = $this->setOrderNo($sheet, $data);
        // 会社情報：会社名
        $sheet = $this->setCompanyName($sheet, $office_info);
        // 会社情報：郵便番号
        $sheet = $this->setCompanyPostalCode($sheet, $office_info);
        // 会社情報：住所
        $sheet = $this->setCompanyAddress($sheet, $office_info);
        // 会社情報：TEL番号
        $sheet = $this->setCompanyTelNumber($sheet, $office_info);

        // 会社情報：FAX番号
        return $this->setCompanyFaxNumber($sheet, $office_info);
    }

    /**
     * 納品先名の設定
     *
     * @param Worksheet $sheet
     * @param OrdersReceived $orders_received
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDelivName(Worksheet $sheet, OrdersReceived $orders_received): Worksheet
    {
        // セル位置取得
        $cell_string = 'B';
        $adjust_row = 3;
        $cell_b1 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip, $adjust_row);
        $cell_b2 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $cell_b3 = $this->getTargetCell($cell_string, $this->start_row_deliv_receipt, $adjust_row);

        // セル範囲取得
        $cell_string = 'Z';
        $adjust_row = 4;
        $range_b1 = $this->getTargetRange($cell_b1, $cell_string, $this->start_row_deliv_slip, $adjust_row);
        $range_b2 = $this->getTargetRange($cell_b2, $cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $range_b3 = $this->getTargetRange($cell_b3, $cell_string, $this->start_row_deliv_receipt, $adjust_row);

        $honorific_title = (new MasterHonorificTitle())->name_fixed;

        if ($orders_received->recipient_name) {
            // 納品書（控）
            $sheet->mergeCells($range_b1);
            $sheet->getStyle($range_b1)->getFont()->setSize(18);
            $sheet->getStyle($range_b1)->getAlignment()->setHorizontal('left');
            $sheet->setCellValue($cell_b1, $orders_received->recipient_name . '　' . $honorific_title);

            // 納品書
            $sheet->mergeCells($range_b2);
            $sheet->getStyle($range_b2)->getFont()->setSize(18);
            $sheet->getStyle($range_b2)->getAlignment()->setHorizontal('left');
            $sheet->setCellValue($cell_b2, $orders_received->recipient_name . '　' . $honorific_title);

            // 物品受領書
            $sheet->mergeCells($range_b3);
            $sheet->getStyle($range_b3)->getFont()->setSize(18);
            $sheet->getStyle($range_b3)->getAlignment()->setHorizontal('left');
            $sheet->setCellValue($cell_b3, $orders_received->recipient_name . '　' . $honorific_title);

            return $sheet;
        }

        $cell_1 = 'X4';
        $range_b1 = $cell_1 . ':Z5';
        $cell_2 = 'X25';
        $range_b2 = $cell_2 . ':Z26';
        $cell_3 = 'X46';
        $range_b3 = $cell_3 . ':Z47';

        // 納品書（控）
        $sheet->mergeCells($range_b1);
        $sheet->getStyle($range_b1)->getFont()->setSize(18);
        $sheet->getStyle($range_b1)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell_1, $honorific_title);

        // 納品書
        $sheet->mergeCells($range_b2);
        $sheet->getStyle($range_b2)->getFont()->setSize(18);
        $sheet->getStyle($range_b2)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell_2, $honorific_title);

        // 物品受領書
        $sheet->mergeCells($range_b3);
        $sheet->getStyle($range_b3)->getFont()->setSize(18);
        $sheet->getStyle($range_b3)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell_3, $honorific_title);

        return $sheet;
    }

    /**
     * 年月日の設定
     *
     * @param Worksheet $sheet
     * @param OrdersReceivedDetail $orders_received_detail
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDate(Worksheet $sheet, OrdersReceivedDetail $orders_received_detail): Worksheet
    {
        // セル位置取得
        $cell_string = 'AD';
        $adjust_row = 2;
        $cell_b1 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip, $adjust_row);
        $cell_b2 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $cell_b3 = $this->getTargetCell($cell_string, $this->start_row_deliv_receipt, $adjust_row);

        // セル範囲取得
        $cell_string = 'AT';
        $range_b1 = $this->getTargetRange($cell_b1, $cell_string, $this->start_row_deliv_slip, $adjust_row);
        $range_b2 = $this->getTargetRange($cell_b2, $cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $range_b3 = $this->getTargetRange($cell_b3, $cell_string, $this->start_row_deliv_receipt, $adjust_row);

        // 伝票日付
        if ($orders_received_detail->delivery_date) {
            $date = Carbon::parse($orders_received_detail->delivery_date)->format('Y年m月d日');

            // 納品書（控）
            $sheet->mergeCells($range_b1);
            $sheet->getStyle($range_b1)->getAlignment()->setHorizontal(Align::VERTICAL_DISTRIBUTED);
            $sheet->setCellValue($cell_b1, $date);

            // 納品書
            $sheet->mergeCells($range_b2);
            $sheet->getStyle($range_b2)->getAlignment()->setHorizontal(Align::VERTICAL_DISTRIBUTED);
            $sheet->setCellValue($cell_b2, $date);

            // 物品受領書
            $sheet->mergeCells($range_b3);
            $sheet->getStyle($range_b3)->getAlignment()->setHorizontal(Align::VERTICAL_DISTRIBUTED);
            $sheet->setCellValue($cell_b3, $date);
        }

        return $sheet;
    }

    /**
     * 伝票番号の設定
     *
     * @param Worksheet $sheet
     * @param OrdersReceived $orders_received
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setOrderNo(Worksheet $sheet, OrdersReceived $orders_received): Worksheet
    {
        // セル位置取得
        $cell_string = 'BO';
        $adjust_row = 1;
        $cell_b1 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip, $adjust_row);
        $cell_b2 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $cell_b3 = $this->getTargetCell($cell_string, $this->start_row_deliv_receipt, $adjust_row);

        // セル範囲取得
        $cell_string = 'BW';
        $range_b1 = $this->getTargetRange($cell_b1, $cell_string, $this->start_row_deliv_slip, $adjust_row);
        $range_b2 = $this->getTargetRange($cell_b2, $cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $range_b3 = $this->getTargetRange($cell_b3, $cell_string, $this->start_row_deliv_receipt, $adjust_row);

        // 納品書（控）
        $sheet->mergeCells($range_b1);
        $sheet->getStyle($range_b1)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($range_b1)->getFont()->setSize(14);
        $sheet->setCellValue($cell_b1, $orders_received->order_number_zero_fill);

        // 納品書
        $sheet->mergeCells($range_b2);
        $sheet->getStyle($range_b2)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($range_b2)->getFont()->setSize(14);
        $sheet->setCellValue($cell_b2, $orders_received->order_number_zero_fill);

        // 物品受領書
        $sheet->mergeCells($range_b3);
        $sheet->getStyle($range_b3)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($range_b3)->getFont()->setSize(14);
        $sheet->setCellValue($cell_b3, $orders_received->order_number_zero_fill);

        return $sheet;
    }

    /**
     * 会社情報・会社名の設定
     *
     * @param Worksheet $sheet
     * @param Collection<MasterHeadOfficeInfo> $office_info
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setCompanyName(Worksheet $sheet, Collection $office_info): Worksheet
    {
        // セル位置取得
        $cell_string = 'AX';
        $adjust_row = 3;
        $cell_b1 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip, $adjust_row);
        $cell_b2 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $cell_b3 = $this->getTargetCell($cell_string, $this->start_row_deliv_receipt, $adjust_row);

        // セル範囲取得
        $cell_string = 'BP';
        $adjust_row = 4;
        $range_b1 = $this->getTargetRange($cell_b1, $cell_string, $this->start_row_deliv_slip, $adjust_row);
        $range_b2 = $this->getTargetRange($cell_b2, $cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $range_b3 = $this->getTargetRange($cell_b3, $cell_string, $this->start_row_deliv_receipt, $adjust_row);

        foreach ($office_info as $office) {
            // 会社名
            // 納品書（控）
            $sheet->mergeCells($range_b1);
            $sheet->getStyle($range_b1)->getFont()->setSize(18);
            $sheet->getStyle($range_b1)->getFont()->setBold(true);
            $sheet->getStyle($range_b1)->getAlignment()->setHorizontal(Align::VERTICAL_DISTRIBUTED);
            $sheet->setCellValue($cell_b1, $office->company_name);

            // 納品書
            $sheet->mergeCells($range_b2);
            $sheet->getStyle($range_b2)->getFont()->setSize(18);
            $sheet->getStyle($range_b2)->getFont()->setBold(true);
            $sheet->getStyle($range_b2)->getAlignment()->setHorizontal(Align::VERTICAL_DISTRIBUTED);
            $sheet->setCellValue($cell_b2, $office->company_name);

            // 物品受領書
            $sheet->mergeCells($range_b3);
            $sheet->getStyle($range_b3)->getFont()->setSize(18);
            $sheet->getStyle($range_b3)->getFont()->setBold(true);
            $sheet->getStyle($range_b3)->getAlignment()->setHorizontal(Align::VERTICAL_DISTRIBUTED);
            $sheet->setCellValue($cell_b3, $office->company_name);
        }

        return $sheet;
    }

    /**
     * 会社情報・郵便番号の設定
     *
     * @param Worksheet $sheet
     * @param Collection<MasterHeadOfficeInfo> $office_info
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setCompanyPostalCode(Worksheet $sheet, Collection $office_info): Worksheet
    {
        // セル位置取得
        $cell_string = 'AT';
        $adjust_row = 6;
        $cell_b1 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip, $adjust_row);
        $cell_b2 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $cell_b3 = $this->getTargetCell($cell_string, $this->start_row_deliv_receipt, $adjust_row);

        // セル範囲取得
        $cell_string = 'AY';
        $range_b1 = $this->getTargetRange($cell_b1, $cell_string, $this->start_row_deliv_slip, $adjust_row);
        $range_b2 = $this->getTargetRange($cell_b2, $cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $range_b3 = $this->getTargetRange($cell_b3, $cell_string, $this->start_row_deliv_receipt, $adjust_row);

        foreach ($office_info as $office) {
            // 郵便番号
            // 納品書（控）
            $sheet->mergeCells($range_b1);
            $sheet->getStyle($range_b1)->getFont()->setSize(12);
            $sheet->getStyle($range_b1)->getAlignment()->setHorizontal('left');
            $postal_code = '';
            if (!empty($office->postal_code1) && !empty($office->postal_code2)) {
                $postal_code = '〒' . $office->postal_code1 . '-' . $office->postal_code2;
            }
            $sheet->setCellValue($cell_b1, $postal_code);

            // 納品書
            $sheet->mergeCells($range_b2);
            $sheet->getStyle($range_b2)->getFont()->setSize(12);
            $sheet->getStyle($range_b2)->getAlignment()->setHorizontal('left');
            $postal_code = '';
            if (!empty($office->postal_code1) && !empty($office->postal_code2)) {
                $postal_code = '〒' . $office->postal_code1 . '-' . $office->postal_code2;
            }
            $sheet->setCellValue($cell_b2, $postal_code);

            // 物品受領書
            $sheet->mergeCells($range_b3);
            $sheet->getStyle($range_b3)->getFont()->setSize(12);
            $sheet->getStyle($range_b3)->getAlignment()->setHorizontal('left');
            $postal_code = '';
            if (!empty($office->postal_code1) && !empty($office->postal_code2)) {
                $postal_code = '〒' . $office->postal_code1 . '-' . $office->postal_code2;
            }
            $sheet->setCellValue($cell_b3, $postal_code);
        }

        return $sheet;
    }

    /**
     * 会社情報・住所の設定
     *
     * @param Worksheet $sheet
     * @param Collection<MasterHeadOfficeInfo> $office_info
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setCompanyAddress(Worksheet $sheet, Collection $office_info): Worksheet
    {
        // セル位置取得
        $cell_string = 'BA';
        $adjust_row = 6;
        $cell_b1 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip, $adjust_row);
        $cell_b2 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $cell_b3 = $this->getTargetCell($cell_string, $this->start_row_deliv_receipt, $adjust_row);

        // セル範囲取得
        $cell_string = 'BW';
        $range_b1 = $this->getTargetRange($cell_b1, $cell_string, $this->start_row_deliv_slip, $adjust_row);
        $range_b2 = $this->getTargetRange($cell_b2, $cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $range_b3 = $this->getTargetRange($cell_b3, $cell_string, $this->start_row_deliv_receipt, $adjust_row);

        foreach ($office_info as $office) {
            // 住所
            // 納品書（控）
            $sheet->mergeCells($range_b1);
            $sheet->getStyle($range_b1)->getFont()->setSize(12);
            $sheet->getStyle($range_b1)->getAlignment()->setHorizontal('left');
            $sheet->setCellValue($cell_b1, $office->address1 . $office->address2);

            // 納品書
            $sheet->mergeCells($range_b2);
            $sheet->getStyle($range_b2)->getFont()->setSize(12);
            $sheet->getStyle($range_b2)->getAlignment()->setHorizontal('left');
            $sheet->setCellValue($cell_b2, $office->address1 . $office->address2);

            // 物品受領書
            $sheet->mergeCells($range_b3);
            $sheet->getStyle($range_b3)->getFont()->setSize(12);
            $sheet->getStyle($range_b3)->getAlignment()->setHorizontal('left');
            $sheet->setCellValue($cell_b3, $office->address1 . $office->address2);
        }

        return $sheet;
    }

    /**
     * 会社情報・電話番号の設定
     *
     * @param Worksheet $sheet
     * @param Collection<MasterHeadOfficeInfo> $office_info
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setCompanyTelNumber(Worksheet $sheet, Collection $office_info): Worksheet
    {
        // セル位置取得
        $cell_string = 'AY';
        $adjust_row = 7;
        $cell_b1 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip, $adjust_row);
        $cell_b2 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $cell_b3 = $this->getTargetCell($cell_string, $this->start_row_deliv_receipt, $adjust_row);

        // セル範囲取得
        $cell_string = 'BK';
        $range_b1 = $this->getTargetRange($cell_b1, $cell_string, $this->start_row_deliv_slip, $adjust_row);
        $range_b2 = $this->getTargetRange($cell_b2, $cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $range_b3 = $this->getTargetRange($cell_b3, $cell_string, $this->start_row_deliv_receipt, $adjust_row);

        foreach ($office_info as $office) {
            // 電話番号
            // 納品書（控）
            $sheet->mergeCells($range_b1);
            $sheet->getStyle($range_b1)->getFont()->setSize(12);
            $sheet->getStyle($range_b1)->getAlignment()->setHorizontal('left');
            $tel_number = '';
            if (!empty($office->tel_number)) {
                $tel_number = 'TEL: ' . $office->tel_number;
            }
            $sheet->setCellValue($cell_b1, $tel_number);

            // 納品書
            $sheet->mergeCells($range_b2);
            $sheet->getStyle($range_b2)->getFont()->setSize(12);
            $sheet->getStyle($range_b2)->getAlignment()->setHorizontal('left');
            $tel_number = '';
            if (!empty($office->tel_number)) {
                $tel_number = 'TEL: ' . $office->tel_number;
            }
            $sheet->setCellValue($cell_b2, $tel_number);

            // 物品受領書
            $sheet->mergeCells($range_b3);
            $sheet->getStyle($range_b3)->getFont()->setSize(12);
            $sheet->getStyle($range_b3)->getAlignment()->setHorizontal('left');
            $tel_number = '';
            if (!empty($office->tel_number)) {
                $tel_number = 'TEL: ' . $office->tel_number;
            }
            $sheet->setCellValue($cell_b3, $tel_number);
        }

        return $sheet;
    }

    /**
     * 会社情報・FAX番号の設定
     *
     * @param Worksheet $sheet
     * @param Collection<MasterHeadOfficeInfo> $office_info
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setCompanyFaxNumber(Worksheet $sheet, Collection $office_info): Worksheet
    {
        // セル位置取得
        $cell_string = 'BL';
        $adjust_row = 7;
        $cell_b1 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip, $adjust_row);
        $cell_b2 = $this->getTargetCell($cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $cell_b3 = $this->getTargetCell($cell_string, $this->start_row_deliv_receipt, $adjust_row);

        // セル範囲取得
        $cell_string = 'BW';
        $range_b1 = $this->getTargetRange($cell_b1, $cell_string, $this->start_row_deliv_slip, $adjust_row);
        $range_b2 = $this->getTargetRange($cell_b2, $cell_string, $this->start_row_deliv_slip_copy, $adjust_row);
        $range_b3 = $this->getTargetRange($cell_b3, $cell_string, $this->start_row_deliv_receipt, $adjust_row);

        foreach ($office_info as $office) {
            // FAX番号
            // 納品書（控）
            $sheet->mergeCells($range_b1);
            $sheet->getStyle($range_b1)->getFont()->setSize(12);
            $sheet->getStyle($range_b1)->getAlignment()->setHorizontal('left');
            $fax_number = '';
            if (!empty($office->fax_number)) {
                $fax_number = 'FAX: ' . $office->fax_number;
            }
            $sheet->setCellValue($cell_b1, $fax_number);

            // 納品書
            $sheet->mergeCells($range_b2);
            $sheet->getStyle($range_b2)->getFont()->setSize(12);
            $sheet->getStyle($range_b2)->getAlignment()->setHorizontal('left');
            $fax_number = '';
            if (!empty($office->fax_number)) {
                $fax_number = 'FAX: ' . $office->fax_number;
            }
            $sheet->setCellValue($cell_b2, $fax_number);

            // 物品受領書
            $sheet->mergeCells($range_b3);
            $sheet->getStyle($range_b3)->getFont()->setSize(12);
            $sheet->getStyle($range_b3)->getAlignment()->setHorizontal('left');
            $fax_number = '';
            if (!empty($office->fax_number)) {
                $fax_number = 'FAX: ' . $office->fax_number;
            }
            $sheet->setCellValue($cell_b3, $fax_number);
        }

        return $sheet;
    }

    /**
     * 明細行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param int $row_count 行数
     * @param OrdersReceivedDetail $orders_received_detail 伝票情報
     * @return Worksheet
     */
    private function setDetailData(Worksheet $sheet, int $row_count,
        OrdersReceivedDetail $orders_received_detail): Worksheet
    {
        // 商品明細
        $start_row_detail = 10;

        // ■納品書（控）
        $row = ($this->start_row_deliv_slip + $start_row_detail + $row_count);

        // 商品名
        $sheet->setCellValue("B$row", $orders_received_detail->product_name);
        // 数量
        $cell = "AV$row:AY$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue("AV$row", $orders_received_detail->quantity);
        // 備考
        $sheet->setCellValue("AZ$row", $orders_received_detail->note);

        // ■納品書
        $row = ($this->start_row_deliv_slip_copy + $start_row_detail + $row_count);
        // 商品名
        $sheet->setCellValue("B$row", $orders_received_detail->product_name);
        // 数量
        $cell = "AV$row:AY$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue("AV$row", $orders_received_detail->quantity);
        // 備考
        $sheet->setCellValue("AZ$row", $orders_received_detail->note);

        // ■物品受領書
        $row = ($this->start_row_deliv_receipt + $start_row_detail + $row_count);
        // 商品名
        $sheet->setCellValue("B$row", $orders_received_detail->product_name);
        // 数量
        $cell = "AV$row:AY$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue("AV$row", $orders_received_detail->quantity);

        return $sheet;
    }

    /**
     * セルの取得
     *
     * @param string $cell_string
     * @param int $row_base
     * @param int $adjust_row
     * @return string
     */
    private function getTargetCell(string $cell_string, int $row_base, int $adjust_row): string
    {
        return $cell_string . ($row_base + $adjust_row);
    }

    /**
     * セル範囲の取得
     *
     * @param string $start_cell
     * @param string $end_cell_string
     * @param int $row_base
     * @param int $adjust_row
     * @return string
     */
    private function getTargetRange(string $start_cell, string $end_cell_string, int $row_base, int $adjust_row): string
    {
        return $start_cell . ':' . $end_cell_string . ($row_base + $adjust_row);
    }
}
