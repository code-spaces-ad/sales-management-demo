<?php

/**
 * 得意先元帳画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Sale\Ledger;

use App\Consts\SessionConst;
use App\Enums\DepositMethodType;
use App\Enums\OrderType;
use App\Helpers\DateHelper;
use App\Helpers\PdfHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\Ledger\CustomerSearchRequest;
use App\Models\Invoice\ChargeData;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterHeadOfficeInfo;
use App\Models\Sale\Ledger\LedgerCustomer;
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
 * 得意先元帳画面用コントローラー
 */
class CustomerController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /** 1ページの最大明細行数 */
    protected $max_row_count;

    /** 合計欄の明細行数 */
    protected $total_row_count;

    /** 明細行の行位置 */
    protected $detail_row;

    /** 得意先名の行位置 */
    protected $customer_name_row;

    /** 年月日の行位置 */
    protected $now_date_row;

    /** ページ番号の行位置 */
    protected $page_no_row;

    /** 住所の行位置 */
    protected $address_row;

    /** 会社名の行位置 */
    protected $company_name_row;

    /** 電話番号の行位置 */
    protected $tel_row;

    /** 締日の行位置 */
    protected $close_date_row;

    /** 回収の行位置 */
    protected $collect_row;

    /** 税計算の行位置 */
    protected $tax_calc_row;

    /** 出力期間の行位置 */
    protected $order_date_row;

    /** 諸税の行位置 */
    protected $tax_total_row;

    /** 売上計の行位置 */
    protected $sub_total_row;

    /**
     * CustomerController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // 1ページの最大明細行数
        $this->max_row_count = 27;
        // 合計欄の明細行数
        $this->total_row_count = 4;
        // 明細行の行位置
        $this->detail_row = 9;
        // 得意先名の行位置
        $this->customer_name_row = 2;
        // 年月日の行位置
        $this->now_date_row = 2;
        // ページ番号の行位置
        $this->page_no_row = 2;
        // 住所の行位置
        $this->address_row = 3;
        // 会社名の行位置
        $this->company_name_row = 3;
        // 電話番号の行位置
        $this->tel_row = 4;
        // 締日の行位置
        $this->close_date_row = 4;
        // 回収の行位置
        $this->collect_row = 4;
        // 税計算の行位置
        $this->tax_calc_row = 4;
        // 出力期間の行位置
        $this->order_date_row = 5;
        // 諸税の行位置
        $this->tax_total_row = 60;
        // 売上計の行位置
        $this->sub_total_row = 62;
    }

    /**
     * Show the application dashboard.
     *
     * @param CustomerSearchRequest $request
     * @return View
     */
    public function index(CustomerSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        // 検索結果の取得
        $search_result = LedgerCustomer::getOrderPaginate($search_condition_input_data);

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('sort_code')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'order_details' => $search_result,
                'carryover' => ChargeData::getLastCarryover($search_condition_input_data),
            ],
            'deposit_method_types' => DepositMethodType::asSelectArray(),
        ];

        return view('sale.ledger.customers.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param CustomerSearchRequest $request
     * @return StreamedResponse
     *
     * @throws Exception
     */
    public function downloadExcel(CustomerSearchRequest $request): StreamedResponse
    {
        $spreadsheet = $this->getSpreadSheet($request);

        // Excelファイル名
        $filename = Carbon::now()->format('YmdHis') . '_' .
            config('consts.excel.filename.ledger_customers');

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
     * @param CustomerSearchRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function showPdf(CustomerSearchRequest $request): RedirectResponse
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
        if ($ret) {
            // 売掛台帳画面にリダイレクト
            $message = config('consts.message.common.show_pdf_failed');

            return redirect(route('sale.ledger.customers'))
                ->with(['message' => $message, 'error_flag' => true]);
        }

        // PDFファイルURLにリダイレクト
        return redirect(asset('/') . config('consts.pdf.temp_path') . $pdf_file_name);
    }

    /**
     * Excelデータ作成
     *
     * @param CustomerSearchRequest $request
     * @return Spreadsheet
     *
     * @throws Exception
     */
    private function getSpreadSheet(CustomerSearchRequest $request): Spreadsheet
    {
        $search_condition_input_data = $request->validated();

        // テンプレートファイルの読み込み
        $path = storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.ledger_customers')
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
        $customers = MasterCustomer::get();
        if ($search_condition_input_data['customer_id'] > 0) {
            $customers = MasterCustomer::where('id', $search_condition_input_data['customer_id'])->get();
        }

        foreach ($customers as $customer) {
            // 得意先情報取得
            $search_condition_input_data['customer_id'] = $customer->id;
            $search_result = LedgerCustomer::getOrder($search_condition_input_data);

            // ページ数
            $page_no = 1;

            // 編集対象のシート取得
            $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no, $customer);

            // ページ共通のデータを設定
            $active_sheet = $this->setPublicPageData(
                $active_sheet,
                $page_no,
                $office_info,
                $search_condition_input_data,
                $customer
            );

            // 明細行の行数
            $row_count = 0;
            $carry_over = ChargeData::getPreviousMonthCarryOver($search_condition_input_data);

            $active_sheet = $this->setBalanceCarriedForward($active_sheet, $carry_over);

            ++$row_count;

            // 合計
            $sub_total = 0;
            $sub_total_tax = 0;
            $deposit_total = 0;
            $balance_carried_forward_total = 0;

            // 商品台帳作成
            foreach ($search_result as $detail_data) {
                if ($detail_data->order_kind === OrderType::DEPOSIT) {
                    // 入金
                    $deposit_total += $detail_data->deposit_total;
                    $arrDeposit = LedgerCustomer::getDepositPaymentDetail1($detail_data);
                    for ($i = 0; $i < count($arrDeposit); ++$i) {
                        if ($arrDeposit[$i]['amount'] != 0) {
                            $active_sheet = $this->setDepositDetailData($active_sheet, $row_count, $detail_data, $i, $arrDeposit[$i]['amount'], $arrDeposit[$i]['note']);
                            ++$row_count;
                        }
                        if ($row_count >= $this->max_row_count) {
                            ++$page_no;

                            // 編集対象のシート取得
                            $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no, $customer);

                            // ページ共通のデータを設定
                            $active_sheet = $this->setPublicPageData(
                                $active_sheet,
                                $page_no,
                                $office_info,
                                $search_condition_input_data,
                                $customer
                            );

                            $row_count = 0;
                        }
                    }
                } else {
                    // 現売、掛売
                    $sub_total += $detail_data->sub_total;

                    // 消費税
                    $sub_total_tax += $detail_data->sub_total_tax;

                    // 明細行
                    $active_sheet = $this->setDetailData($active_sheet, $row_count, $detail_data, $customer);
                    ++$row_count;
                }

                // １ページの最大明細行を超えた場合かつ、まだデータが存在する場合
                if ($row_count >= $this->max_row_count) {
                    ++$page_no;

                    // 編集対象のシート取得
                    $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no, $customer);

                    // ページ共通のデータを設定
                    $active_sheet = $this->setPublicPageData(
                        $active_sheet,
                        $page_no,
                        $office_info,
                        $search_condition_input_data,
                        $customer
                    );

                    $row_count = 0;
                }
            }

            // 最終ページの明細行数を取得
            $extra = count($search_result) % $this->max_row_count;
            if ($extra > ($this->max_row_count - $this->total_row_count)) {
                ++$page_no;

                // 編集対象のシート取得
                $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no, $customer);

                // ページ共通のデータを設定
                $active_sheet = $this->setPublicPageData(
                    $active_sheet,
                    $page_no,
                    $office_info,
                    $search_condition_input_data,
                    $customer
                );
            }

            // 合計
            $this->setDetailTotalData(
                $active_sheet,
                $sub_total,
                $deposit_total,
                $balance_carried_forward_total,
                $search_condition_input_data['order_date'],
                $sub_total_tax
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
     * 得意先名の設定
     *
     * @param Worksheet $sheet シート情報
     * @param object $customer_data 得意先情報
     * @return Worksheet
     */
    private function setCustomerName(Worksheet $sheet, object $customer_data): Worksheet
    {
        $row = $this->customer_name_row;

        // 得意先コード
        $cell = "H{$row}";
        $sheet->getStyle($cell)->getFont()->setSize(18);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $customer_data->code_zerofill . ':');

        // 得意先名
        $cell = "I{$row}";
        $sheet->getStyle($cell)->getFont()->setSize(18);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $customer_data->name);

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
        $cell = "Q{$row}";
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
        $cell = "T{$row}";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($cell, $page_no);

        return $sheet;
    }

    /**
     * 住所の設定
     *
     * @param Worksheet $sheet シート情報
     * @param object $customer_data 得意先情報
     * @return Worksheet
     */
    private function setAddress(Worksheet $sheet, object $customer_data): Worksheet
    {
        $row = $this->address_row;

        // 住所
        $cell = "B{$row}";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $customer_data->address);

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
            $cell = "O{$row}";
            $sheet->getStyle($cell)->getFont()->setSize(11);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
            $sheet->setCellValue($cell, $office->company_name);
        }

        return $sheet;
    }

    /**
     * 電話番号の設定
     *
     * @param Worksheet $sheet シート情報
     * @param object $customer_data 得意先情報
     * @return Worksheet
     */
    private function setTel(Worksheet $sheet, object $customer_data): Worksheet
    {
        $row = $this->tel_row;

        // 住所
        $cell = "B{$row}";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $customer_data->tel_number);

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
        $sheet->setCellValue($cell, $start_date . '～' . $end_date);

        return $sheet;
    }

    /**
     * 繰越残高の設定
     *
     * @param Worksheet $sheet シート情報
     * @param object|null $carry
     * @return Worksheet
     */
    private function setBalanceCarriedForward(Worksheet $sheet, ?object $carry): Worksheet
    {
        $row = $this->detail_row + 1;

        // 繰越残高ラベル
        $cell = "D{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, '＊＊繰越残高＊＊');

        $cell = "P{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $carry->carryover ?? 0);

        return $sheet;
    }

    /**
     * 明細行の設定(入金)
     *
     * @param Worksheet $sheet シート情報
     * @param int $row_count 行数
     * @param object $order_detail
     * @param int $payment_type
     * @param int $amount
     * @param string $note
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDepositDetailData(Worksheet $sheet, int $row_count, object $order_detail, int $payment_type, int $amount, ?string $note): Worksheet
    {
        $row = $this->detail_row + ($row_count * 2);

        // 1行目
        // 伝票日付
        $date = DateHelper::getFullShortJpDate($order_detail->order_date);

        $cell = "A$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $date);

        // 備考
        $cell = "Q$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $note);

        ++$row;

        // 2行目
        // 伝票番号
        $cell = "A$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $order_detail->order_number);

        // 種別
        $cell = "B$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($cell, OrderType::getDescription($order_detail->order_kind));

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
            $payment_name = '手形';
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

        // 商品名
        $cell = "D$row:I$row";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue("D$row", $payment_name);

        // 入金
        $cell = "O$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, number_format($amount));

        return $sheet;
    }

    /**
     * 明細行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param int $row_count 行数
     * @param object $order_detail 伝票情報
     * @param object $customer 得意先情報
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDetailData(Worksheet $sheet, int $row_count, object $order_detail, object $customer): Worksheet
    {
        $row = $this->detail_row + ($row_count * 2);

        // 1行目
        // 伝票日付
        $date = DateHelper::getFullShortJpDate($order_detail->order_date);

        $cell = "A{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $date);

        // 商品コード
        $cell = "D{$row}:I{$row}";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue("D{$row}", $order_detail->product_code);

        // 備考
        $cell = "Q{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $order_detail->note);

        ++$row;

        // 2行目
        // 伝票番号
        $cell = "A{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $order_detail->order_number);

        // 種別
        $cell = "B{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($cell, OrderType::getDescription($order_detail->order_kind));

        // 支所名
        $cell = "C{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($cell, $order_detail->branch_n);

        // 商品名
        $cell = "D{$row}:I{$row}";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue("D{$row}", $order_detail->product_name);

        // 数量
        $cell = "J{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $formatQuantity = '#,##0';
        $quantity_decimal_digit = $order_detail->quantity_decimal_digit;
        if ($quantity_decimal_digit > 0) {
            $formatQuantity = $formatQuantity . sprintf(".%0{$quantity_decimal_digit}d", 0);
        }
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($formatQuantity);
        $sheet->setCellValue($cell, $order_detail->quantity);

        // 単位
        $cell = "K{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($cell, $order_detail->unit_name);

        // 単価
        $cell = "L{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $formatUnitPrice = '#,##0';
        $unit_price_decimal_digit = $order_detail->unit_price_decimal_digit;
        if ($unit_price_decimal_digit > 0) {
            $formatUnitPrice = $formatUnitPrice . sprintf(".%0{$unit_price_decimal_digit}d", 0);
        }
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($formatUnitPrice);
        $sheet->setCellValue($cell, $order_detail->unit_price);

        if ($order_detail->order_kind === OrderType::DEPOSIT) {
            // 入金
            $cell = "O{$row}";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->setCellValue($cell, $order_detail->deposit_total);
        } else {
            // 現売、掛売
            $cell = "M{$row}";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->setCellValue($cell, $order_detail->sub_total);
        }

        if (!($order_detail->order_kind === OrderType::DEPOSIT)) {
            // 消費税
            $cell = "N{$row}";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->setCellValue($cell, $order_detail->sub_total_tax);
        }

        return $sheet;
    }

    /**
     * 明細合計行の設定
     *
     * @param Worksheet $sheet
     * @param $sub_total
     * @param $deposit_total
     * @param $balance_carried_forward_total
     * @param $order_date
     * @param $sub_total_tax
     * @return void
     *
     * @throws Exception
     */
    private function setDetailTotalData(Worksheet $sheet, $sub_total, $deposit_total, $balance_carried_forward_total, $order_date, $sub_total_tax): void
    {
        $row = $this->tax_total_row;

        // 諸税
        $cell = "C{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, '諸税');

        $cell = "D{$row}:I{$row}";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue("D{$row}", '請求書単位消費税');

        $row = $this->sub_total_row;

        // 合計
        $cell = "D{$row}:I{$row}";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue("D{$row}", '＊＊　　　計　　　＊＊');

        // 売上
        $cell = "M{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $sub_total);

        // 消費税
        $cell = "N{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $sub_total_tax);

        // 入金
        $cell = "O{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $deposit_total);

        $cell = "P{$row}";
        $sheet->setCellValue($cell, '');
    }

    /**
     * 編集対象のシート取得
     *
     * @param Spreadsheet $spreadsheet
     * @param Worksheet $sheet
     * @param $page_no
     * @param $customer
     * @return Worksheet|null
     *
     * @throws Exception
     */
    private function getActiveSheet(Spreadsheet $spreadsheet, Worksheet $sheet, $page_no, $customer): ?Worksheet
    {
        // シートの複製
        $cloned_sheet = clone $sheet;

        // シート名
        $sheet_name = '得意先元帳_' . $customer->code_zerofill . '_' . $page_no;
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
     * @param $customer
     * @return Worksheet
     */
    private function setPublicPageData(Worksheet $sheet, $page_no, $office_info, $search_condition_input_data, $customer): Worksheet
    {
        // 得意先名
        $sheet = $this->setCustomerName($sheet, $customer);

        // 年月日
        $sheet = $this->setDate($sheet);

        // ページ番号
        $sheet = $this->setPageNo($sheet, $page_no);

        // 住所情報
        $sheet = $this->setAddress($sheet, $customer);

        // 会社情報
        $sheet = $this->setCompany($sheet, $office_info);

        // 電話番号情報
        $sheet = $this->setTel($sheet, $customer);

        // 出力期間
        return $this->setOrderDate($sheet, $search_condition_input_data['order_date']);
    }
}
