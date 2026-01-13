<?php

/**
 * 得意先別売上表画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Sale\Ledger;

use App\Consts\SessionConst;
use App\Helpers\DateHelper;
use App\Helpers\PdfHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\Ledger\SalesCustomerSearchRequest;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterHeadOfficeInfo;
use App\Models\Sale\Ledger\LedgerSalesCustomer;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 得意先別売上表画面用コントローラー
 */
class SalesCustomerController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /** 1ページの領域 */
    protected $max_page_height;

    /** 1ページの最大明細行数 */
    protected $max_row_count;

    /** 合計欄の明細行数 */
    protected $total_row_count;

    /** 明細行の行位置 */
    protected $detail_row;

    /** 年月日の行位置 */
    protected $now_date_row;

    /** ページ番号の行位置 */
    protected $page_no_row;

    /** タイトルの行位置 */
    protected $title_row;

    /** 会社名の行位置 */
    protected $company_name_row;

    /** 出力期間の行位置 */
    protected $order_date_row;

    /** 税額の行位置 */
    protected $tax_total_row;

    /** 売上計の行位置 */
    protected $sub_total_row;

    /**
     * SalesCustomerController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // 1ページの領域
        $this->max_page_height = 35;

        // 1ページの最大明細行数
        $this->max_row_count = 13;

        // 合計欄の明細行数
        $this->total_row_count = 2;

        // 明細行の行位置
        $this->detail_row = 7;

        // 年月日の行位置
        $this->now_date_row = 2;
        // ページ番号の行位置
        $this->page_no_row = 2;
        // タイトルの行位置
        $this->title_row = 3;
        // 会社名の行位置
        $this->company_name_row = 3;
        // 出力期間の行位置
        $this->order_date_row = 4;

        // 税額の行位置
        $this->tax_total_row = 30;
        // 売上計の行位置
        $this->sub_total_row = 32;
    }

    /**
     * Show the application dashboard.
     *
     * @param SalesCustomerSearchRequest $request
     * @return View
     */
    public function index(SalesCustomerSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 検索項目 */
            'search_items' => [
                'customers' => MasterCustomer::query()->oldest('code')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'order_details' => LedgerSalesCustomer::getOrder($search_condition_input_data),
            ],
        ];

        return view('sale.ledger.sales_customers.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param SalesCustomerSearchRequest $request
     * @return StreamedResponse
     *
     * @throws Exception
     */
    public function downloadExcel(SalesCustomerSearchRequest $request): StreamedResponse
    {
        $spreadsheet = $this->getSpreadSheet($request);

        // Excelファイル名
        $filename = Carbon::now()->format('YmdHis') . '_' . config('consts.excel.filename.ledger_sales_customer');

        // Output
        $callback = function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        };

        $status = 200;
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment;filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ];

        return new StreamedResponse($callback, $status, $headers);
    }

    /**
     * PDF表示（Excel -> PDF変換）
     *
     * @param SalesCustomerSearchRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function showPdf(SalesCustomerSearchRequest $request): RedirectResponse
    {
        $spreadsheet = $this->getSpreadSheet($request);

        $user_id = Auth::user()->id_zerofill ?? '0000000000';
        $file_name = date('YmdHis');
        $excel_file_name = "{$file_name}_{$user_id}.xlsx";
        $pdf_file_name = "{$file_name}_{$user_id}.pdf";

        // 一旦、Excelファイルを保存
        $excel_path = storage_path(config('consts.excel.temp_path')) . $excel_file_name;
        $writer = new Xlsx($spreadsheet);
        $writer->save($excel_path);

        // Excel -> PDF 変換
        $pdf_dir = public_path(config('consts.pdf.temp_path'));
        $ret = PdfHelper::convertPdf($excel_path, $pdf_dir);
        if ($ret !== 0) {
            // 得意先別売上表画面用にリダイレクト
            $message = config('consts.message.common.show_pdf_failed');

            return redirect(route('sale.ledger.sales_customers'))
                ->with(['message' => $message, 'error_flag' => true]);
        }

        // PDFファイルURLにリダイレクト
        return redirect(asset('/') . config('consts.pdf.temp_path') . $pdf_file_name);
    }

    /**
     * Excelデータ作成
     *
     * @param SalesCustomerSearchRequest $request
     * @return Spreadsheet
     *
     * @throws Exception
     */
    private function getSpreadSheet(SalesCustomerSearchRequest $request): Spreadsheet
    {
        $search_condition_input_data = $request->validated();

        $path = storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.ledger_sales_customer')
        );
        $spreadsheet = IOFactory::load($path);

        // シートの設定
        $sheet = $spreadsheet->getActiveSheet();
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

        // 会社情報
        $office_info = MasterHeadOfficeInfo::fixedOnly()->get();

        // 表示データ取得
        $search_result = LedgerSalesCustomer::getOrder($search_condition_input_data);

        // ページ数
        $page_no = 1;

        // 編集対象のシート取得
        $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no);

        // ページ共通のデータを設定
        $active_sheet = $this->setPublicPageData($active_sheet, $page_no, $office_info, $search_condition_input_data);

        // 明細行の行数
        $row_count = 0;

        // 合計
        $sub_total = 0;

        // 商品別売上表作成
        foreach ($search_result as $key => $detail_data) {
            // 合計
            $sub_total += $detail_data->sales_total;

            // 明細行
            $active_sheet = $this->setDetailData($active_sheet, $row_count, $detail_data);

            ++$row_count;

            // １ページの最大明細行を超えた場合かつ、まだデータが存在する場合
            if ($row_count >= $this->max_row_count && count($search_result) > ($this->max_row_count * $page_no)) {
                ++$page_no;

                // 編集対象のシート取得
                $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no);

                // ページ共通のデータを設定
                $active_sheet = $this->setPublicPageData(
                    $active_sheet,
                    $page_no,
                    $office_info,
                    $search_condition_input_data
                );

                $row_count = 0;
            }
        }

        // 最終ページの明細行数を取得
        $extra = count($search_result) % $this->max_row_count;
        if ($extra > ($this->max_row_count - $this->total_row_count)) {
            ++$page_no;

            // 編集対象のシート取得
            $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no);

            // ページ共通のデータを設定
            $active_sheet = $this->setPublicPageData(
                $active_sheet,
                $page_no,
                $office_info,
                $search_condition_input_data
            );
        }

        // 合計
        $active_sheet = $this->setDetailTotalData($active_sheet, $sub_total);

        if ($spreadsheet->getSheetCount() > 1) {
            // 先頭のシートを削除
            $spreadsheet->removeSheetByIndex(0);
        }

        // 先頭のシートをアクティブにする
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * タイトルの設定
     *
     * @param Worksheet $sheet シート情報
     * @return Worksheet
     */
    private function setTitle(Worksheet $sheet): Worksheet
    {
        $row = $this->title_row;

        // ページ番号
        $cell = "A{$row}";
        $sheet->getStyle($cell)->getFont()->setSize(16);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, '得意先別売上表');

        return $sheet;
    }

    /**
     * 年月日の設定
     *
     * @param Worksheet $sheet シート情報
     * @return Worksheet
     */
    private function setDate(Worksheet $sheet): Worksheet
    {
        $row = $this->now_date_row;

        $date = new Carbon('now');
        $date_string = DateHelper::getFullJpDate($date->format('Y-m-d'));

        // 年月日
        $cell = "M{$row}";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $date_string);

        return $sheet;
    }

    /**
     * ページ番号の設定
     *
     * @param Worksheet $sheet シート情報
     * @param int $page_no ページNo
     * @return Worksheet
     */
    private function setPageNo(Worksheet $sheet, int $page_no): Worksheet
    {
        $row = $this->page_no_row;

        // ページ番号
        $cell = "O{$row}";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($cell, $page_no);

        return $sheet;
    }

    /**
     * 会社名の設定
     *
     * @param Worksheet $sheet シート情報
     * @param object $office_info 会社情報
     * @return Worksheet
     */
    private function setCompany(Worksheet $sheet, object $office_info): Worksheet
    {
        foreach ($office_info as $office) {
            $row = $this->company_name_row;

            // 会社名
            $cell = "K{$row}";
            $sheet->getStyle($cell)->getFont()->setSize(11);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
            $sheet->setCellValue($cell, $office->company_name);
        }

        return $sheet;
    }

    /**
     * 出力期間の設定
     *
     * @param Worksheet $sheet シート情報
     * @param array $order_date 出力期間
     * @return Worksheet
     */
    private function setOrderDate(Worksheet $sheet, array $order_date): Worksheet
    {
        $row = $this->order_date_row;

        $start_date = DateHelper::getFullJpDate($order_date['start']);
        $end_date = DateHelper::getFullJpDate($order_date['end']);

        // 出力期間
        $cell = "B{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $start_date);

        $cell = "E{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $end_date);

        return $sheet;
    }

    /**
     * 明細行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param int $row_count 行数
     * @param Collection $order_detail 伝票情報
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDetailData(Worksheet $sheet, int $row_count, $order_detail): Worksheet
    {
        $row = $this->detail_row + ($row_count * 2);

        // 得意先コード
        $cell = "A{$row}:C{$row}";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue("A{$row}", $order_detail->customer_code);

        // 粗利率(％)
        $cell = "M{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $order_detail->gross_profit_margin);

        // 構成比(％)
        $cell = "N{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $order_detail->composition_ratio);

        // 順位
        $cell = "O{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $order_detail->rank);

        ++$row;

        // 得意先名
        $cell = "A{$row}:C{$row}";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue("A{$row}", $order_detail->c_name);

        // 売上金額
        $cell = "D{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->sales_total);

        // 小計
        $cell = "H{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->sales_total);

        // 売上金額計
        $cell = "J{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, '');

        return $sheet;
    }

    /**
     * 明細合計行の設定
     *
     * @param Worksheet $sheet
     * @param $sub_total
     */
    private function setDetailTotalData(Worksheet $sheet, $sub_total): void
    {
        // 税額
        $row = $this->tax_total_row;

        $cell = "A{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, '税額');

        // 合計
        $row = $this->sub_total_row;

        $cell = "A{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, '※　合計　※');

        $cell = "D{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $sub_total);

        $cell = "H{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $sub_total);
    }

    /**
     * 編集対象のシート取得
     *
     * @param Spreadsheet $spreadsheet
     * @param Worksheet $sheet
     * @param $page_no
     * @return Worksheet|null
     *
     * @throws Exception
     */
    private function getActiveSheet(Spreadsheet $spreadsheet, Worksheet $sheet, $page_no): ?Worksheet
    {
        // シートの複製
        $cloned_sheet = clone $sheet;

        // シート名
        $sheet_name = '得意先別売上表_' . $page_no;
        $cloned_sheet->setTitle($sheet_name);

        // シートの追加
        $spreadsheet->addSheet($cloned_sheet);

        // 編集対象のシート取得
        return $spreadsheet->getSheetByName($sheet_name);
    }

    /**
     * ページ共通の設定値を設定
     *
     * @param Worksheet $sheet
     * @param $page_no
     * @param $office_info
     * @param $search_condition_input_data
     * @return Worksheet
     */
    private function setPublicPageData(Worksheet $sheet, $page_no, $office_info, $search_condition_input_data): Worksheet
    {
        // タイトル
        $sheet = $this->setTitle($sheet);

        // 年月日
        $sheet = $this->setDate($sheet);

        // ページ番号
        $sheet = $this->setPageNo($sheet, $page_no);

        // 会社情報
        $sheet = $this->setCompany($sheet, $office_info);

        // 出力期間
        $sheet = $this->setOrderDate($sheet, $search_condition_input_data['order_date']);

        return $sheet;
    }
}
