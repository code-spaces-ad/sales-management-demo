<?php

namespace App\Services\Excel;

use App\Enums\OrderType;
use App\Http\Requests\Invoice\InvoicePrintRequest;
use App\Models\Invoice\ChargeData;
use App\Models\Invoice\ChargeDetail;
use App\Models\Master\MasterHeadOfficeInfo;
use App\Models\Sale\SalesOrder;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesInvoiceBranchSummaryExcelService extends SalesInvoiceExcelService
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
        $charge_data = ChargeData::query()->where('id', $charge_data_id)->get();
        $sales_invoice_printing_method = 0;

        // 請求書作成(請求データ毎のループ）※元々複数請求データ処理だったため
        foreach ($charge_data as $data) {
            // ヘッダ頁：編集対象のシート取得
            $active_sheet_h = SalesInvoiceCommonService::getActiveSheet(
                $spreadsheet,
                $spreadsheet->getSheetbyName('template_a'),
                $data->customer_id
            );

            // ヘッダ頁：ヘッダ部スタイル設定
            $this->setStyleHeaderPageTopRow($active_sheet_h, $output_type);

            // ヘッダ頁：共通データ設定
            $this->setDataHeaderPageTopRow($active_sheet_h, $office_info, $data, $issue_date[0]);

            // ヘッダ頁：消費税データ設定 + 現在行セット
            $current_row = $this->setTaxTotalData($active_sheet_h, $data);
            // ヘッダ頁：列タイトル行
            $this->setStyleColumnTitleRow($active_sheet_h, $current_row, $current_row, 1);
            ++$current_row;

            // ヘッダ頁：明細部
            $order_details = ChargeDetail::getOrderDetail($data);
            $back_color_reverse = -1;
            $current_branch_id = 0;
            $constr_site_sales_sub_total = 0;
            $constr_site_deposit_sub_total = 0;
            $count_sales_order_details = 0;
            foreach ($order_details as $order_detail) {
                // 支所を出力（小計行もここで出力）
                if (!empty($order_detail->branch_id) && $current_branch_id != $order_detail->branch_id) {
                    if ($constr_site_sales_sub_total !== 0 || $constr_site_deposit_sub_total !== 0) {
                        $this->setStyleDetailRow($active_sheet_h, $current_row, $back_color_reverse, '0');
                        $this->setCustomerDetailDataTotalRow(
                            $active_sheet_h,
                            $current_row++,
                            $constr_site_sales_sub_total,
                            $constr_site_deposit_sub_total);
                        $back_color_reverse *= -1;
                    }
                    $this->setStyleDetailRow($active_sheet_h, $current_row, $back_color_reverse, '0');
                    $back_color_reverse *= -1;
                    $this->setCustomerDataDetailRow($active_sheet_h, $current_row, $order_detail);
                    ++$current_row;
                    $current_branch_id = $order_detail->branch_id;
                    $constr_site_sales_sub_total = 0;
                    $constr_site_deposit_sub_total = 0;
                }
                if ($order_detail->order_type == OrderType::DEPOSIT) {
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
                    $constr_site_deposit_sub_total +=
                        (empty($order_detail->deposit_total) ? 0 : $order_detail->deposit_total);
                } else {
                    ++$count_sales_order_details;
                    // 文字色設定
                    $fore_color_type = '0';
                    if ($order_detail->order_type == 2) {
                        if ($order_detail->consumption_tax_rate == 0) {
                            $fore_color_type = '2';
                        } else {
                            if ($order_detail->tax_type_id == 2) {
                                $fore_color_type = '1';
                            }
                        }
                    }
                    $this->setStyleDetailRow($active_sheet_h, $current_row, $back_color_reverse, $fore_color_type);
                    $back_color_reverse *= -1;
                    // 明細行
                    $this->setConstrSiteDetailDataDetailRow($active_sheet_h, $current_row++, $order_detail);
                    $constr_site_sales_sub_total +=
                        (empty($order_detail->sub_total) ? 0 : $order_detail->sub_total);
                }

            }
            $this->setStyleDetailRow($active_sheet_h, $current_row, $back_color_reverse, '0');
            $this->setCustomerDetailDataTotalRow(
                $active_sheet_h,
                $current_row++,
                $constr_site_sales_sub_total,
                $constr_site_deposit_sub_total);
            $back_color_reverse *= -1;

            if ($count_sales_order_details > 0) {
                // ヘッダ頁：税額サマリー行（６行）
                ++$current_row;
                $this->setStyleDetailRow($active_sheet_h, $current_row, $back_color_reverse, '0');
                // $current_row++;
                for ($i = $current_row; $i <= $current_row; ++$i) {
                    $ret_rows = $this->setConstrSiteSummaryTaxDetailRow($active_sheet_h, $i, $data, $back_color_reverse);
                }
                $current_row = $i + $ret_rows;
            }
        }
        // 印刷範囲の設定
        SalesInvoiceCommonService::setPagePrintArea($active_sheet_h, $current_row, $sales_invoice_printing_method);

        // 先頭のシートをアクティブにする
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * 明細頁（請求一覧）～子得意先別～合計行の設定
     *
     * @param Worksheet $sheet
     * @param int $current_row
     * @param int $sales_sub_total
     * @param int $deposit_sub_total
     * @return void
     */
    private function setCustomerDetailDataTotalRow(Worksheet $sheet,
        int $current_row,
        int $sales_sub_total,
        int $deposit_sub_total): void
    {
        // 小計行見出し
        $row_title_name = '＝＝＝ 小　計 ＝＝＝';

        // 合計額
        $sales_total = empty($sales_sub_total) ? '' : number_format($sales_sub_total);
        $deposit_total = empty($deposit_sub_total) ? '' : number_format($deposit_sub_total);

        // 値のセット
        $sheet
            ->getStyle("M{$current_row}:M{$current_row}")
            ->getAlignment()
            ->setHorizontal('right');
        $sheet
            ->setCellValue("C{$current_row}", '')
            ->setCellValue("H{$current_row}", '')
            ->setCellValue("M{$current_row}", $row_title_name)
            ->setCellValue("AE{$current_row}", '')
            ->setCellValue("AH{$current_row}", '')
            ->setCellValue("AK{$current_row}", '')
            ->setCellValue("AP{$current_row}", $sales_total)
            ->setCellValue("AW{$current_row}", '')
            ->setCellValue("BA{$current_row}", $deposit_total)
            ->setCellValue("BG{$current_row}", '');
        $cell = 'C' . $current_row . ':' . 'BO' . $current_row;
        $sheet->getStyle($cell)->getAlignment()->setShrinkToFit(true);
        $sheet->getStyle($cell)->getFont()->setSize(16);
    }

    /**
     * 明細頁（支所別一覧）～"支所"明細行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param int $current_row
     * @param ?SalesOrder $constr_site_order 伝票情報
     * @return void
     */
    private function setCustomerDataDetailRow(Worksheet $sheet, int $current_row, ?SalesOrder $constr_site_order): void
    {
        $sheet
            ->setCellValue("C{$current_row}", '')
            ->setCellValue("H{$current_row}", '')
            ->setCellValue("M{$current_row}", '【' . $constr_site_order->branch_name . '】')
            ->setCellValue("AE{$current_row}", '')
            ->setCellValue("AH{$current_row}", '')
            ->setCellValue("AK{$current_row}", '')
            ->setCellValue("AP{$current_row}", '')
            ->setCellValue("AW{$current_row}", '')
            ->setCellValue("BA{$current_row}", '')
            ->setCellValue("BG{$current_row}", '');
        $cell = 'C' . $current_row . ':' . 'BO' . $current_row;
        $sheet->getStyle($cell)->getAlignment()->setShrinkToFit(true);
        $sheet->getStyle($cell)->getFont()->setSize(16);
    }

    /**
     * 消費税合計行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param ChargeData $charge_data 請求情報
     * @return int
     *
     * @throws Exception
     */
    private function setTaxTotalData(Worksheet $sheet, ChargeData $charge_data): int
    {
        $row = 32;
        $tax_summary_format = config('consts.default.sales_invoice_print.tax_summary_format');
        $tax_list = [
            'normal_out' => [
                'text' => '税別計10%',
                'total' => $charge_data->sales_total_normal_out,
                'tax' => $charge_data->sales_tax_normal_out,
            ],
            'reduced_out' => [
                'text' => '税別計軽減8%',
                'total' => $charge_data->sales_total_reduced_out,
                'tax' => $charge_data->sales_tax_reduced_out,
            ],
            'normal_in' => [
                'text' => '税込計10%',
                'total' => $charge_data->sales_total_normal_in,
                'tax' => $charge_data->sales_tax_normal_in,
            ],
            'reduced_in' => [
                'text' => '税込計軽減8%',
                'total' => $charge_data->sales_total_reduced_in,
                'tax' => $charge_data->sales_tax_reduced_in,
            ],
            'tax_free' => [
                'text' => '非課税',
                'total' => $charge_data->sales_total_free,
                'tax' => 0,
            ],
        ];
        foreach ($tax_list as $values) {
            if ($tax_summary_format !== '0' && $values['total'] === 0) {
                continue;
            }
            if ($values['total'] === 0) {
                continue;
            }
            $tax_rate_text_cell = "T$row";
            $sales_total_cell = "AB$row";
            $tax_total_cell = "AJ$row";
            $total_cell = "AR$row";
            // セルの行幅
            $sheet->getRowDimension($row)->setRowHeight(30);
            // セルのフォントサイズ
            $sheet->getStyle($row)->getFont()->setSize(16);
            // セルのマージ
            $sheet
                ->mergeCells("$tax_rate_text_cell:AA$row")
                ->mergeCells("$sales_total_cell:AI$row")
                ->mergeCells("$tax_total_cell:AQ$row")
                ->mergeCells("$total_cell:AY$row");
            // 罫線
            $sheet
                ->getStyle("$tax_rate_text_cell:AY$row")
                ->applyFromArray(PrintExcelCommonService::$arrStyleThinInsideHair);
            // ヘッダ部・合計欄
            $sheet
                ->setCellValue($tax_rate_text_cell, $values['text'])
                ->setCellValue($sales_total_cell, number_format($values['total']))
                ->setCellValue($tax_total_cell, number_format($values['tax']))
                ->setCellValue($total_cell, $values['total'] + $values['tax']);

            $sheet->getStyle($tax_rate_text_cell)->getAlignment()->setShrinkToFit(true)->setHorizontal('center');
            $sheet->getStyle($sales_total_cell)->getAlignment()->setShrinkToFit(true)->setHorizontal('right');
            $sheet->getStyle($tax_total_cell)->getAlignment()->setShrinkToFit(true)->setHorizontal('right');
            $sheet->getStyle($total_cell)->getAlignment()->setShrinkToFit(true)->setHorizontal('right');

            ++$row;
        }

        return ++$row;
    }
}
