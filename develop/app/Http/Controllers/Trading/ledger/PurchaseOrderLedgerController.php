<?php

/**
 * 仕入台帳参照画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Trading\ledger;

use App\Consts\SessionConst;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethodType;
use App\Helpers\DateHelper;
use App\Helpers\PdfHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Trading\PurchaseSearchRequest;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterHeadOfficeInfo;
use App\Models\Master\MasterSupplier;
use App\Models\PurchaseInvoice\PurchaseClosing;
use App\Models\Trading\Ledger\LedgerPurchaseOrder;
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
 * 仕入台帳参照画面用コントローラー
 */
class PurchaseOrderLedgerController extends Controller
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
    protected $supplier_name_row;

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

    /** 出力期間の行位置 */
    protected $order_date_row;

    /** 仕入計の行位置 */
    protected $purchase_total_row;

    /**
     * PurchaseOrderLedgerController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // 1ページの最大明細行数
        $this->max_row_count = 13;
        // 合計欄の明細行数
        $this->total_row_count = 1;
        // 明細行の行位置
        $this->detail_row = 9;
        // 得意先名の行位置
        $this->supplier_name_row = 2;
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
        // 出力期間の行位置
        $this->order_date_row = 5;
        // 仕入計の行位置
        $this->purchase_total_row = 34;
    }

    /**
     * Show the application dashboard.
     *
     * @param PurchaseSearchRequest $request
     * @return View
     */
    public function index(PurchaseSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        // 担当者データ
        $employees = MasterEmployee::query()->oldest('code')->get();
        // 仕入先データ
        $suppliers = MasterSupplier::query()->oldest('name_kana')->get();

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 担当者マスター */
                'employees' => $employees,
                /** 仕入先マスター */
                'suppliers' => $suppliers,
                /** 状態 */
                'order_status' => OrderStatus::asSelectArray(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'purchase_orders_sd' => LedgerPurchaseOrder::getPurchaseOrderPaginate($search_condition_input_data),
            ],
            'payment_method_types' => PaymentMethodType::asSelectArray(),
        ];

        return view('trading.ledger.purchase_orders_sd.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param PurchaseSearchRequest $request
     * @return StreamedResponse
     *
     * @throws Exception
     */
    public function downloadExcel(PurchaseSearchRequest $request): StreamedResponse
    {
        $spreadsheet = $this->getSpreadSheet($request);

        // Excelファイル名
        $filename = Carbon::now()->format('YmdHis') . '_' .
            config('consts.excel.filename.ledger_purchase_orders');

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
     * @param PurchaseSearchRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function showPdf(PurchaseSearchRequest $request): RedirectResponse
    {
        $spreadsheet = $this->getSpreadSheet($request);

        $user_id = Auth::user()->id_zerofill ?? '0000000000';
        $file_name = date('YmdHis');
        $excel_file_name = "{$file_name}_$user_id.xlsx";
        $pdf_file_name = "{$file_name}_$user_id.pdf";

        // 一旦、Excelファイルを保存
        $excel_path = storage_path(config('consts.excel.temp_path')) . $excel_file_name;
        $writer = new Xlsx($spreadsheet);
        $writer->save($excel_path);

        // Excel -> PDF 変換
        $pdf_dir = public_path(config('consts.pdf.temp_path'));
        $ret = PdfHelper::convertPdf($excel_path, $pdf_dir);
        if ($ret) {
            // 仕入台帳画面にリダイレクト
            $message = config('consts.message.common.show_pdf_failed');

            return redirect(route('trading.ledger.purchase_orders_sd'))
                ->with(['message' => $message, 'error_flag' => true]);
        }

        // PDFファイルURLにリダイレクト
        return redirect(asset('/') . config('consts.pdf.temp_path') . $pdf_file_name);
    }

    /**
     * Excelデータ作成
     *
     * @param PurchaseSearchRequest $request
     * @return Spreadsheet
     *
     * @throws Exception
     */
    private function getSpreadSheet(PurchaseSearchRequest $request): Spreadsheet
    {
        $search_condition_input_data = $request->validated();

        // テンプレートファイルの読み込み
        $path = storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.ledger_purchase_orders')
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
        $suppliers = MasterSupplier::get();
        if ($search_condition_input_data['supplier_id'] > 0) {
            $suppliers = MasterSupplier::where('id', $search_condition_input_data['supplier_id'])->get();
        }

        // 仕入台帳出力時のソート
        $sort = 'asc';

        foreach ($suppliers as $supplier) {
            // 仕入先情報取得
            $search_condition_input_data['supplier_id'] = $supplier->id;
            $search_result = LedgerPurchaseOrder::getPurchaseOrder($search_condition_input_data, $sort);

            // ページ数
            $page_no = 1;

            // 編集対象のシート取得
            $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no, $supplier);

            // ページ共通のデータを設定
            $active_sheet = $this->setPublicPageData(
                $active_sheet,
                $page_no,
                $office_info,
                $search_condition_input_data,
                $supplier
            );

            // 明細行の行数
            $row_count = 0;
            $carry_over = PurchaseClosing::getPreviousMonthCarryOver($search_condition_input_data);
            $active_sheet = $this->setBalanceCarriedForward($active_sheet, $carry_over);

            ++$row_count;

            // 合計
            $purchase_total = 0;
            $payment_total = 0;
            $purchase_total_tax = 0;

            // 仕入台帳作成
            foreach ($search_result as $detail_data) {
                if ($detail_data->order_kind === OrderType::PAYMENT) {
                    // 支払
                    $payment_total += $detail_data->payment;
                    $arrPayment = LedgerPurchaseOrder::getDepositPaymentDetail1($detail_data);
                    for ($i = 0; $i < count($arrPayment); ++$i) {
                        if ($arrPayment[$i]['amount'] != 0) {
                            $active_sheet = $this->setPaymentDetailData($active_sheet, $row_count, $detail_data, $i, $arrPayment[$i]['amount'], $arrPayment[$i]['note']);
                            ++$row_count;
                        }
                        if ($row_count >= $this->max_row_count) {
                            ++$page_no;

                            // 編集対象のシート取得
                            $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no, $supplier);

                            // ページ共通のデータを設定
                            $active_sheet = $this->setPublicPageData(
                                $active_sheet,
                                $page_no,
                                $office_info,
                                $search_condition_input_data,
                                $supplier
                            );

                            $row_count = 0;
                        }
                    }
                } else {
                    // 仕入
                    $purchase_total += $detail_data->purchase_total;

                    $purchase_total_tax += $detail_data->sub_total_tax;

                    // 明細行
                    $active_sheet = $this->setDetailData($active_sheet, $row_count, $detail_data);
                    ++$row_count;
                }

                // １ページの最大明細行を超えた場合かつ、まだデータが存在する場合
                if ($row_count >= $this->max_row_count) {
                    ++$page_no;

                    // 編集対象のシート取得
                    $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no, $supplier);

                    // ページ共通のデータを設定
                    $active_sheet = $this->setPublicPageData(
                        $active_sheet,
                        $page_no,
                        $office_info,
                        $search_condition_input_data,
                        $supplier
                    );

                    $row_count = 0;
                }
            }

            // 合計
            $this->setDetailTotalData(
                $active_sheet,
                $purchase_total,
                $payment_total,
                $search_condition_input_data['order_date'],
                $purchase_total_tax
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
     * 仕入先名の設定
     *
     * @param Worksheet $sheet シート情報
     * @param object $supplier_data
     * @return Worksheet
     */
    private function setSupplierName(Worksheet $sheet, object $supplier_data): Worksheet
    {
        $row = $this->supplier_name_row;

        // 仕入先コード
        $cell = "H$row";
        $sheet->getStyle($cell)->getFont()->setSize(18);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $supplier_data->code_zerofill . ':');

        // 仕入先名
        $cell = "I$row";
        $sheet->getStyle($cell)->getFont()->setSize(18);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $supplier_data->name);

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
        $cell = "O$row";
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
        $cell = "Q$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($cell, $page_no);

        return $sheet;
    }

    /**
     * 住所の設定
     *
     * @param Worksheet $sheet シート情報
     * @param object $supplier_data
     * @return Worksheet
     */
    private function setAddress(Worksheet $sheet, object $supplier_data): Worksheet
    {
        $row = $this->address_row;

        // 住所
        $cell = "B$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $supplier_data->address);

        return $sheet;
    }

    /**
     * 会社名の設定
     *
     * @param Worksheet $sheet シート情報
     * @param object $office_info
     * @return Worksheet
     */
    private function setCompany(Worksheet $sheet, object $office_info): Worksheet
    {
        foreach ($office_info as $office) {
            $row = $this->company_name_row;

            // 会社名
            $cell = "N$row";
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
     * @param object $supplier_data
     * @return Worksheet
     */
    private function setTel(Worksheet $sheet, object $supplier_data): Worksheet
    {
        $row = $this->tel_row;

        // 住所
        $cell = "B$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $supplier_data->tel_number);

        return $sheet;
    }

    /**
     * 出力期間の設定
     *
     * @param Worksheet $sheet シート情報
     * @param array $order_date
     * @return Worksheet
     */
    private function setOrderDate(Worksheet $sheet, array $order_date): Worksheet
    {
        $row = $this->order_date_row;

        $start_date = DateHelper::getFullJpDate($order_date['start']);
        $end_date = DateHelper::getFullJpDate($order_date['end']);

        // 出力期間
        $cell = "B$row";
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
        $cell = "C{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, '＊＊繰越残高＊＊');

        $cell = "O{$row}";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $carry->carryover ?? 0);

        return $sheet;
    }

    /**
     * 明細行の設定(支払い)
     *
     * @param Worksheet $sheet シート情報
     * @param int $row_count 行数
     * @param object $order_detail
     * @param int $payment_type
     * @param int $amount
     * @param ?string $note
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setPaymentDetailData(Worksheet $sheet, int $row_count, object $order_detail, int $payment_type, int $amount, ?string $note): Worksheet
    {
        $row = $this->detail_row + ($row_count * 2);

        // 1行目
        // 伝票日付
        $date = DateHelper::getFullShortJpDate($order_detail->order_date);

        $cell = "A$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $date);

        // 備考
        $cell = "P$row";
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
        $cell = "C$row:H$row";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue("C$row", $payment_name);

        // 支払
        $cell = "N$row";
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
     * @param object $order_detail
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDetailData(Worksheet $sheet, int $row_count, object $order_detail): Worksheet
    {
        $row = $this->detail_row + ($row_count * 2);

        // 1行目
        // 伝票日付
        $date = DateHelper::getFullShortJpDate($order_detail->order_date);

        $cell = "A$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $date);

        // 商品コード
        $cell = "C$row:H$row";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue("C$row", $order_detail->product_code);

        // 備考
        $cell = "P$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $order_detail->note);

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

        // 商品名
        $cell = "C$row:H$row";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue("C$row", $order_detail->product_name);

        // 数量
        if ($order_detail->order_kind === OrderType::PURCHASE) {
            $cell = "I$row";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
            $formatQuantity = '#,##0';
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($formatQuantity);
            $sheet->setCellValue($cell, $order_detail->quantity);
        }

        // 単位
        $cell = "J$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($cell, $order_detail->unit_name);

        // 単価
        if ($order_detail->order_kind === OrderType::PURCHASE) {
            $cell = "K$row";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
            $formatUnitPrice = '#,##0';
            $unit_price_decimal_digit = $order_detail->unit_price_decimal_digit;
            if ($unit_price_decimal_digit > 0) {
                $formatUnitPrice = $formatUnitPrice . sprintf(".%0{$unit_price_decimal_digit}d", 0);
            }
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($formatUnitPrice);
            $sheet->setCellValue($cell, $order_detail->unit_price);
        }

        if ($order_detail->order_kind === OrderType::PAYMENT) {
            // 支払
            $cell = "N$row";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->setCellValue($cell, $order_detail->payment);
        } else {
            // 仕入
            $cell = "L$row";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->setCellValue($cell, $order_detail->purchase_total);
        }

        // 消費税
        $cell = "M$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $order_detail->sub_total_tax);

        return $sheet;
    }

    /**
     * 明細合計行の設定
     *
     * @param Worksheet $sheet
     * @param int $purchase_total
     * @param int $payment_total
     * @param array $order_date
     * @param int $purchase_total_tax
     *
     * @throws Exception
     */
    private function setDetailTotalData(Worksheet $sheet, int $purchase_total, int $payment_total, array $order_date, int $purchase_total_tax): void
    {
        $row = $this->purchase_total_row;

        // 合計
        $cell = "C$row:H$row";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue("C$row", '＊＊　　　計　　　＊＊');

        // 仕入
        $cell = "L$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $purchase_total);

        // 消費税合計
        $cell = "M$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $purchase_total_tax);

        // 支払
        $cell = "N$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $payment_total);
    }

    /**
     * 編集対象のシート取得
     *
     * @param Spreadsheet $spreadsheet
     * @param Worksheet $sheet
     * @param int $page_no
     * @param object $supplier
     * @return Worksheet|null
     *
     * @throws Exception
     */
    private function getActiveSheet(Spreadsheet $spreadsheet, Worksheet $sheet, int $page_no, object $supplier): ?Worksheet
    {
        // シートの複製
        $cloned_sheet = clone $sheet;

        // シート名
        $sheet_name = '仕入先台帳_' . $supplier->code_zerofill . '_' . $page_no;
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
     * @param int $page_no
     * @param object $office_info
     * @param array $search_condition_input_data
     * @param object $supplier
     * @return Worksheet
     */
    private function setPublicPageData(Worksheet $sheet, int $page_no, object $office_info,
        array $search_condition_input_data, object $supplier): Worksheet
    {
        // 仕入先名
        $sheet = $this->setSupplierName($sheet, $supplier);

        // 年月日
        $sheet = $this->setDate($sheet);

        // ページ番号
        $sheet = $this->setPageNo($sheet, $page_no);

        // 住所情報
        $sheet = $this->setAddress($sheet, $supplier);

        // 会社情報
        $sheet = $this->setCompany($sheet, $office_info);

        // 電話番号情報
        $sheet = $this->setTel($sheet, $supplier);

        // 出力期間
        return $this->setOrderDate($sheet, $search_condition_input_data['order_date']);
    }
}
