<?php

namespace App\Services\Excel;

use App\Enums\ReducedTaxFlagType;
use App\Helpers\TaxHelper;
use App\Models\Master\MasterHeadOfficeInfo;
use App\Models\Sale\SalesOrder;
use App\Models\Sale\SalesOrderDetail;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment as Align;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderExcelService
{
    /** 各ブロックの開始位置 */
    protected $start_row_deliv_slip = 1;

    protected $start_row_deliv_slip_copy = 23;

    protected $start_row_deliv_receipt = 45;

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
        $max_deliv_slip_page_height = 63;
        // 1ブロックの最大明細行数 */
        $max_detail_row_per_block = 7;

        // テンプレートファイルの読み込み
        $path = storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.sale_delivery_slip_print')
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
        $search_result = SalesOrder::where('id', $request->id)->get();

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

            // 税抜合計
            $total = 0;

            // 消費税8%対象（税込）
            $reduced_total = 0;

            // 消費税10%対象（税込）
            $consumption_total = 0;

            // 税区分別売上（税抜）
            $reduced_total_exc = 0;
            $consumption_total_exc = 0;
            $notax_total_exc = 0;

            // 税込合計
            $sub_total = 0;

            foreach ($data->salesOrderDetail as $detail_data) {
                $unit_price = $detail_data->unit_price;
                $quantity = $detail_data->quantity;
                $consumption_tax_rate = $detail_data->consumption_tax_rate;
                $rounding_method_id = $detail_data->rounding_method_id;
                $reduced_tax_flag = $detail_data->reduced_tax_flag;

                // 税抜小計
                $row_sub_total_exc = $unit_price * $quantity;
                // 税抜小計(税区分別）
                if ($reduced_tax_flag == ReducedTaxFlagType::REDUCED_TAX) {
                    $reduced_total_exc += $row_sub_total_exc;
                } elseif ($reduced_tax_flag == ReducedTaxFlagType::NOT_REDUCED_TAX && $consumption_tax_rate > 0) {
                    $consumption_total_exc += $row_sub_total_exc;
                } else {
                    $notax_total_exc += $row_sub_total_exc;
                }

                // 税込小計
                $row_sub_total = $unit_price * $quantity;
                $total += $row_sub_total;

                // 税率端数処理
                if ($consumption_tax_rate > 0) {
                    $incTax = TaxHelper::getIncTax($row_sub_total, $consumption_tax_rate, $rounding_method_id);

                    $tax = $incTax - $row_sub_total;
                    if ($reduced_tax_flag == ReducedTaxFlagType::REDUCED_TAX) {
                        $reduced_total += $tax;
                    }
                    if ($reduced_tax_flag == ReducedTaxFlagType::NOT_REDUCED_TAX) {
                        $consumption_total += $tax;
                    }
                    $row_sub_total = $incTax;
                }
                $sub_total += $row_sub_total;

                // １ページの最大明細行を超えた場合かつ、まだデータが存在する場合
                if (
                    $row_count >= $max_detail_row_per_block
                    && count($data->salesOrderDetail) > ($max_detail_row_per_block * $page_no)
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
                    $detail_data,
                    $row_sub_total_exc
                );

                ++$row_count;
            }

            // 合計行
            $this->setDetailTotalData(
                $active_sheet,
                $total,
                $consumption_total,
                $sub_total
            );
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
     * @param Collection<MasterHeadOfficeInfo> $office_info
     * @param SalesOrder $data
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setPublicPageData(Worksheet $sheet,
        Collection $office_info, SalesOrder $data): Worksheet
    {
        // 納品先名
        $sheet = $this->setDelivName($sheet, $data);
        // 年月日
        $sheet = $this->setDate($sheet, $data);
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
     * @param SalesOrder $sales_order
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDelivName(Worksheet $sheet, SalesOrder $sales_order): Worksheet
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

        // 納品書（控）
        $sheet->mergeCells($range_b1);
        $sheet->getStyle($range_b1)->getFont()->setSize(18);
        $sheet->getStyle($range_b1)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell_b1, $sales_order->cname_bname_htitle);

        // 納品書
        $sheet->mergeCells($range_b2);
        $sheet->getStyle($range_b2)->getFont()->setSize(18);
        $sheet->getStyle($range_b2)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell_b2, $sales_order->cname_bname_htitle);

        // 物品受領書
        $sheet->mergeCells($range_b3);
        $sheet->getStyle($range_b3)->getFont()->setSize(18);
        $sheet->getStyle($range_b3)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell_b3, $sales_order->cname_bname_htitle);

        return $sheet;
    }

    /**
     * 年月日の設定
     *
     * @param Worksheet $sheet
     * @param SalesOrder $sales_order
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDate(Worksheet $sheet, SalesOrder $sales_order): Worksheet
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
        $date = DateTime::createFromFormat('Y-m-d', $sales_order->order_date)->format('Y年m月d日');

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

        return $sheet;
    }

    /**
     * 伝票番号の設定
     *
     * @param Worksheet $sheet
     * @param SalesOrder $sales_order
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setOrderNo(Worksheet $sheet, SalesOrder $sales_order): Worksheet
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
        $sheet->setCellValue($cell_b1, $sales_order->order_number_zero_fill);

        // 納品書
        $sheet->mergeCells($range_b2);
        $sheet->getStyle($range_b2)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($range_b2)->getFont()->setSize(14);
        $sheet->setCellValue($cell_b2, $sales_order->order_number_zero_fill);

        // 物品受領書
        $sheet->mergeCells($range_b3);
        $sheet->getStyle($range_b3)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($range_b3)->getFont()->setSize(14);
        $sheet->setCellValue($cell_b3, $sales_order->order_number_zero_fill);

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
     * @param SalesOrderDetail $sales_order_detail 伝票情報
     * @param int $row_sub_total 小計
     * @return Worksheet
     */
    private function setDetailData(Worksheet $sheet, int $row_count,
        SalesOrderDetail $sales_order_detail, int $row_sub_total): Worksheet
    {
        // 商品明細
        $start_row_detail = 10;

        // ■納品書（控）
        $row = ($this->start_row_deliv_slip + $start_row_detail + $row_count);

        // 商品名
        $sheet->setCellValue("B$row", $sales_order_detail->product_name);
        // 数量
        $cell = "AH$row:AK$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue("AH$row", $sales_order_detail->quantity);
        // 単位
        $cell = "AL$row:AO$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue("AL$row", $sales_order_detail->unit_name);

        $format = '#,##0';
        $decimal_digit = $sales_order_detail->mProduct->unit_price_decimal_digit;
        if ($decimal_digit === 1) {
            $format = '#,##0.0';
        }
        if ($decimal_digit === 2) {
            $format = '#,##0.#0';
        }
        if ($decimal_digit === 3) {
            $format = '#,##0.##0';
        }
        if ($decimal_digit === 4) {
            $format = '#,##0.###0';
        }
        // 単価
        $cell = "AP$row:AU$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($format);
        $sheet->setCellValue("AP$row", $sales_order_detail->unit_price);
        // 売上金額
        $cell = "AV$row:BC$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue("AV$row", $row_sub_total);
        // 備考
        $sheet->setCellValue("BE$row", $sales_order_detail->note);
        // フォントサイズ調整
        $sheet->getStyle("B$row:BL$row")->getAlignment()->setShrinkToFit(true);

        // ■納品書
        $row = ($this->start_row_deliv_slip_copy + $start_row_detail + $row_count);
        // 商品名
        $sheet->setCellValue("B$row", $sales_order_detail->product_name);
        // 数量
        $cell = "AH$row:AK$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue("AH$row", $sales_order_detail->quantity);
        // 単位
        $cell = "AL$row:AO$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue("AL$row", $sales_order_detail->unit_name);
        // 単価
        $cell = "AP$row:AU$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($format);
        $sheet->setCellValue("AP$row", $sales_order_detail->unit_price);
        // 売上金額
        $cell = "AV$row:BC$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue("AV$row", $row_sub_total);
        // 備考
        $sheet->setCellValue("BE$row", $sales_order_detail->note);
        // フォントサイズ調整
        $sheet->getStyle("B$row:BL$row")->getAlignment()->setShrinkToFit(true);

        // ■物品受領書
        $row = ($this->start_row_deliv_receipt + $start_row_detail + $row_count);
        // 商品名
        $sheet->setCellValue("B$row", $sales_order_detail->product_name);
        // 数量
        $cell = "AH$row:AK$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue("AH$row", $sales_order_detail->quantity);
        // 単位
        $cell = "AL$row:AO$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue("AL$row", $sales_order_detail->unit_name);
        // フォントサイズ調整
        $sheet->getStyle("B$row:AL$row")->getAlignment()->setShrinkToFit(true);

        return $sheet;
    }

    /**
     * 合計行の設定
     *
     * @param Worksheet $sheet
     * @param int $total
     * @param int $consumption_total
     * @param int $sub_total
     */
    private function setDetailTotalData(
        Worksheet $sheet,
        int $total, int $consumption_total, int $sub_total)
    {
        // 商品明細
        $start_row_detail = 10;
        // 1ブロックの最大明細行数
        $max_detail_row_per_block = 5;

        // ■納品書（控）
        $row = ($this->start_row_deliv_slip + $start_row_detail + $max_detail_row_per_block);
        // 金額計(左)
        $cell = "AV$row:BC$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('"¥"#,##0');
        $sheet->setCellValue("AV$row", $total);
        // フォントサイズ調整
        $sheet->getStyle("AV$row")->getAlignment()->setShrinkToFit(true);

        // ■納品書
        $row = ($this->start_row_deliv_slip_copy + $start_row_detail + $max_detail_row_per_block);
        // 金額計(左)
        $cell = "AV$row:BC$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('"¥"#,##0');
        $sheet->setCellValue("AV$row", $total);
        // フォントサイズ調整
        $sheet->getStyle("AV$row")->getAlignment()->setShrinkToFit(true);
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
