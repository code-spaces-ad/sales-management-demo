<?php

/**
 * 入金台帳画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Sale\Ledger;

use App\Helpers\DateHelper;
use App\Helpers\PdfHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\Ledger\DepositsSearchRequest;
use App\Models\Master\MasterHeadOfficeInfo;
use App\Models\Sale\Ledger\LedgerDeposit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 入金台帳画面用コントローラー
 */
class DepositController extends Controller
{
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

    /** 売上計の行位置 */
    protected $sub_total_row;

    /**
     * DepositController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // 1ページの領域
        $this->max_page_height = 23;

        // 1ページの最大明細行数
        $this->max_row_count = 15;

        // 合計欄の明細行数
        $this->total_row_count = 1;

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

        // 売上計の行位置
        $this->sub_total_row = 21;
    }

    /**
     * Show the application dashboard.
     *
     * @param DepositsSearchRequest $request
     * @return Response
     */
    public function index(DepositsSearchRequest $request)
    {
        $prev_class_path = Session::get('prev_php_method.class_path', '');
        $prev_function_name = Session::get('prev_php_method.function_name', '');
        $search_condition_input_data = Session::get('search_condition_input_data.ledger_deposits', []);
        if ($prev_class_path !== get_class($this) || $prev_function_name === __FUNCTION__) {
            $search_condition_input_data = $request->validated();
            Session::put('search_condition_input_data.ledger_deposits', $search_condition_input_data);
        }

        // 出力期間（伝票日付）のデフォルトセット
        if (!isset($search_condition_input_data['order_date'])) {
            /** 出力期間（開始）：月初 */
            $search_condition_input_data['order_date']['start'] = Carbon::now()->startOfMonth()->toDateString();
            /** 出力期間（終了）：月末 */
            $search_condition_input_data['order_date']['end'] = Carbon::now()->endOfMonth()->toDateString();
        }

        $data = [
            /** 検索項目 */
            'search_items' => [
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'order_details' => LedgerDeposit::getOrder($search_condition_input_data),
            ],
        ];

        return view('sale.ledger.deposits.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param DepositsSearchRequest $request
     * @return StreamedResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function downloadExcel(DepositsSearchRequest $request)
    {
        $spreadsheet = $this->getSpreadSheet($request);

        // Excelファイル名
        $filename = Carbon::now()->format('YmdHis') . '_' .
            config('consts.excel.filename.ledger_deposit');

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
     * @param DepositsSearchRequest $request
     * @return mixed
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function showPdf(DepositsSearchRequest $request)
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
            // 入金台帳画面にリダイレクト
            $message = config('consts.message.common.show_pdf_failed');

            return redirect(route('sale.ledger.deposits'))
                ->with(['message' => $message, 'error_flag' => true]);
        }

        // PDFファイルURLにリダイレクト
        return redirect(asset('/') . config('consts.pdf.temp_path') . $pdf_file_name);
    }

    /**
     * Excelデータ作成
     *
     * @param DepositsSearchRequest $request
     * @return Spreadsheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function getSpreadSheet(DepositsSearchRequest $request)
    {
        $search_condition_input_data = $request->validated();

        // テンプレートファイルの読み込み
        $path = storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.ledger_deposit')
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
        $search_result = LedgerDeposit::getOrder($search_condition_input_data);

        // ページ数
        $page_no = 1;

        // 編集対象のシート取得
        $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no);

        // ページ共通のデータを設定
        $active_sheet = $this->setPublicPageData($active_sheet, $page_no, $office_info, $search_condition_input_data);

        // 明細行の行数
        $row_count = 0;

        // 合計
        $amount_cash = 0;
        $amount_check = 0;
        $amount_transfer = 0;
        $amount_bill = 0;
        $amount_offset = 0;
        $amount_discount = 0;
        $amount_fee = 0;
        $amount_other = 0;
        $total_deposit = 0;

        // 入金台帳作成
        foreach ($search_result as $key => $detail_data) {
            // 合計
            $amount_cash += str_replace(',', '', $detail_data->amount_cash);
            $amount_check += str_replace(',', '', $detail_data->amount_check);
            $amount_transfer += str_replace(',', '', $detail_data->amount_transfer);
            $amount_bill += str_replace(',', '', $detail_data->amount_bill);
            $amount_offset += str_replace(',', '', $detail_data->amount_offset);
            $amount_discount += str_replace(',', '', $detail_data->amount_discount);
            $amount_fee += str_replace(',', '', $detail_data->amount_fee);
            $amount_other += str_replace(',', '', $detail_data->amount_other);
            $total_deposit += str_replace(',', '', $detail_data->total_deposit);

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
        $row = $this->sub_total_row;

        // 合計ラベル
        $cell = "A{$row}";
        $active_sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $active_sheet->setCellValue($cell, '※　合計　※');

        // 現金
        $cell = "H{$row}";
        $active_sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $active_sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $active_sheet->setCellValue($cell, $amount_cash);

        // 小切手
        $cell = "I{$row}";
        $active_sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $active_sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $active_sheet->setCellValue($cell, $amount_check);

        // 振込
        $cell = "J{$row}";
        $active_sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $active_sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $active_sheet->setCellValue($cell, $amount_transfer);

        // 手形
        $cell = "K{$row}";
        $active_sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $active_sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $active_sheet->setCellValue($cell, $amount_bill);

        // 相殺
        $cell = "L{$row}";
        $active_sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $active_sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $active_sheet->setCellValue($cell, $amount_offset);

        // 値引
        $cell = "M{$row}";
        $active_sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $active_sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $active_sheet->setCellValue($cell, $amount_discount);

        // 手数料
        $cell = "N{$row}";
        $active_sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $active_sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $active_sheet->setCellValue($cell, $amount_fee);

        // その他
        $cell = "O{$row}";
        $active_sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $active_sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $active_sheet->setCellValue($cell, $amount_other);

        // 合計
        $cell = "Q{$row}";
        $active_sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $active_sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $active_sheet->setCellValue($cell, $total_deposit);

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
     * @return mixed
     */
    private function setTitle($sheet)
    {
        $row = $this->title_row;

        // ページ番号
        $cell = "A{$row}";
        $sheet->getStyle($cell)->getFont()->setSize(16);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, '金種別入金一覧表');

        return $sheet;
    }

    /**
     * 年月日の設定
     *
     * @param Worksheet $sheet シート情報
     * @return mixed
     */
    private function setDate($sheet)
    {
        $row = $this->now_date_row;

        $date = new Carbon('now');
        $date_string = DateHelper::getFullJpDate($date->format('Y-m-d'));

        // 年月日
        $cell = "O{$row}";
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
     * @return mixed
     */
    private function setPageNo($sheet, $page_no)
    {
        $row = $this->page_no_row;

        // ページ番号
        $cell = "Q{$row}";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($cell, $page_no);

        return $sheet;
    }

    /**
     * 会社名の設定
     *
     * @param Worksheet $sheet シート情報
     * @param array $office_info 会社情報
     * @return mixed
     */
    private function setCompany($sheet, $office_info)
    {
        foreach ($office_info as $office) {
            $row = $this->company_name_row;

            // 会社名
            $cell = "M{$row}";
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
     * @return mixed
     */
    private function setOrderDate($sheet, $order_date)
    {
        $row = $this->order_date_row;

        $start_date = DateHelper::getFullJpDate($order_date['start']);
        $end_date = DateHelper::getFullJpDate($order_date['end']);

        // 出力期間
        $cell = "C{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $start_date);

        $cell = "F{$row}";
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
     * @return mixed
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function setDetailData($sheet, $row_count, $order_detail)
    {
        $row = $this->detail_row + $row_count;

        // 得意先コード
        $cell = "A{$row}:B{$row}";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue("A{$row}", $order_detail->customer_code);

        // 得意先名
        $cell = "C{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $order_detail->c_name);

        // 現金
        $cell = "H{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->amount_cash);

        // 小切手
        $cell = "I{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->amount_check);

        // 振込
        $cell = "J{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->amount_transfer);

        // 手形
        $cell = "K{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->amount_bill);

        // 相殺
        $cell = "L{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->amount_offset);

        // 値引
        $cell = "M{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->amount_discount);

        // 手数料
        $cell = "N{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->amount_fee);

        // その他
        $cell = "O{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->amount_other);

        // 合計
        $cell = "Q{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->total_deposit);

        return $sheet;
    }

    /**
     * 編集対象のシート取得
     *
     * @param Spreadsheet $spreadsheet
     * @param Worksheet $sheet
     * @param $page_no
     * @param $dept
     * @return mixed
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function getActiveSheet($spreadsheet, $sheet, $page_no)
    {
        // シートの複製
        $cloned_sheet = clone $sheet;

        // シート名
        $sheet_name = '金種別入金一覧表_' . $page_no;
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
     * @return mixed
     */
    private function setPublicPageData($sheet, $page_no, $office_info, $search_condition_input_data)
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
