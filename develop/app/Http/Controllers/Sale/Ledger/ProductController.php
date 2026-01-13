<?php

/**
 * 商品台帳画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Sale\Ledger;

use App\Consts\SessionConst;
use App\Enums\OrderType;
use App\Helpers\DateHelper;
use App\Helpers\PdfHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\Ledger\ProductsSearchRequest;
use App\Models\Master\MasterHeadOfficeInfo;
use App\Models\Master\MasterProduct;
use App\Models\Sale\Ledger\LedgerProduct;
use App\Models\Sale\Ledger\LedgerStocks;
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
 * 商品台帳画面用コントローラー
 */
class ProductController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /** 1ページの最大明細行数 */
    protected $max_row_count;

    /** 合計欄の明細行数 */
    protected $total_row_count;

    /** 明細行の行位置 */
    protected $detail_row;

    /** 商品名の行位置 */
    protected $product_name_row;

    /** 年月日の行位置 */
    protected $now_date_row;

    /** ページ番号の行位置 */
    protected $page_no_row;

    /** 会社名の行位置 */
    protected $company_name_row;

    /** 出力期間の行位置 */
    protected $order_date_row;

    /** 売上数量の行位置 */
    protected $total_quantity_row;

    /** 売上合計の行位置 */
    protected $sub_total_row;

    /** 仕入数量の行位置 */
    protected $purchase_total_quantity_row;

    /** 仕入合計の行位置 */
    protected $purchase_sub_total_row;

    /**
     * ProductController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // 1ページの最大明細行数
        $this->max_row_count = 24;

        // 合計欄の明細行数
        $this->total_row_count = 8;

        // 明細行の行位置
        $this->detail_row = 8;

        // 商品名の行位置
        $this->product_name_row = 2;
        // 年月日の行位置
        $this->now_date_row = 2;
        // ページ番号の行位置
        $this->page_no_row = 2;
        // 会社名の行位置
        $this->company_name_row = 3;
        // 出力期間の行位置
        $this->order_date_row = 5;
        // 売上数量の行位置
        $this->total_quantity_row = 53;
        // 売上合計の行位置
        $this->sub_total_row = 55;
        // 仕入数量の行位置
        $this->purchase_total_quantity_row = 49;
        // 仕入合計の行位置
        $this->purchase_sub_total_row = 51;
    }

    /**
     * Show the application dashboard.
     *
     * @param ProductsSearchRequest $request
     * @return View
     */
    public function index(ProductsSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $ledger_stocks = 0;
        if (isset($search_condition_input_data['order_date']['start'])) {
            $ledger_stocks = LedgerStocks::getLastLedgerStocks(
                $search_condition_input_data['product_id'],
                DateHelper::changeDateFormat($search_condition_input_data['order_date']['start'], 'Ym')
            )->ledger_stocks ?? 0;
        }

        // 売上伝票と仕入伝票を結合したデータを取得
        $order_data = LedgerProduct::getOrder($search_condition_input_data);

        $data = [
            /** 検索項目 */
            'search_items' => [
                'products' => MasterProduct::query()->oldest('name_kana')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'order_details' => LedgerProduct::setPaginateForCollection($order_data),
                'sales_quantity' => $order_data->where('order_kind', OrderType::SALES)->sum('quantity') ?? 0,
                'purchase_quantity' => $order_data->where('order_kind', OrderType::PURCHASE)->sum('quantity') ?? 0,
                'ledger_stocks' => $ledger_stocks,
            ],
        ];

        return view('sale.ledger.products.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param ProductsSearchRequest $request
     * @return StreamedResponse
     *
     * @throws Exception
     */
    public function downloadExcel(ProductsSearchRequest $request): StreamedResponse
    {
        $spreadsheet = $this->getSpreadSheet($request);

        // Excelファイル名
        $filename = Carbon::now()->format('YmdHis') . '_' . config('consts.excel.filename.ledger_product');

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
     * @param ProductsSearchRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function showPdf(ProductsSearchRequest $request): RedirectResponse
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
            // 商品台帳画面にリダイレクト
            $message = config('consts.message.common.show_pdf_failed');

            return redirect(route('sale.ledger.products'))
                ->with(['message' => $message, 'error_flag' => true]);
        }

        // PDFファイルURLにリダイレクト
        return redirect(asset('/') . config('consts.pdf.temp_path') . $pdf_file_name);
    }

    /**
     * Excelデータ作成
     *
     * @param ProductsSearchRequest $request
     * @return Spreadsheet
     *
     * @throws Exception
     */
    private function getSpreadSheet(ProductsSearchRequest $request): Spreadsheet
    {
        $search_condition_input_data = $request->validated();

        $path = storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.ledger_product')
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

        // 商品台帳出力時のソート
        $sort = 'asc';

        // 表示データ取得
        $products = MasterProduct::get();
        if ($search_condition_input_data['product_id'] > 0) {
            $products = MasterProduct::where('id', $search_condition_input_data['product_id'])->get();
        }

        foreach ($products as $product) {
            // 商品情報取得
            $search_condition_input_data['product_id'] = $product->id;
            $search_result = LedgerProduct::getOrder($search_condition_input_data, $sort);

            // ページ数
            $page_no = 1;

            // 編集対象のシート取得
            $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no, $product);

            // ページ共通のデータを設定
            $active_sheet = $this->setPublicPageData(
                $active_sheet,
                $page_no,
                $office_info,
                $search_condition_input_data,
                $product
            );

            // 明細行の行数
            $row_count = 0;

            // 合計
            $sub_total = 0;
            $quantity_total = 0;
            $purchase_sub_total = 0;
            $purchase_quantity_total = 0;

            // 商品台帳作成
            foreach ($search_result as $detail_data) {
                if ($detail_data->order_kind === OrderType::SALES) {
                    $sub_total += $detail_data->unit_price * $detail_data->quantity;
                    $quantity_total += $detail_data->quantity;
                }
                if ($detail_data->order_kind === OrderType::PURCHASE) {
                    $purchase_sub_total += $detail_data->unit_price * $detail_data->quantity;
                    $purchase_quantity_total += $detail_data->quantity;
                }

                // 明細行
                $active_sheet = $this->setDetailData($active_sheet, $row_count, $detail_data);

                ++$row_count;

                // １ページの最大明細行を超えた場合かつ、まだデータが存在する場合
                if ($row_count >= $this->max_row_count && count($search_result) >= ($this->max_row_count * $page_no)) {
                    ++$page_no;

                    // 編集対象のシート取得
                    $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no, $product);

                    // ページ共通のデータを設定
                    $active_sheet = $this->setPublicPageData(
                        $active_sheet,
                        $page_no,
                        $office_info,
                        $search_condition_input_data,
                        $product
                    );

                    $row_count = 0;
                }
            }

            // 最終ページの明細行数を取得
            $extra = count($search_result) % $this->max_row_count;
            if ($extra > ($this->max_row_count - $this->total_row_count)) {
                ++$page_no;

                // 編集対象のシート取得
                $active_sheet = $this->getActiveSheet($spreadsheet, $sheet, $page_no, $product);

                // ページ共通のデータを設定
                $active_sheet = $this->setPublicPageData(
                    $active_sheet,
                    $page_no,
                    $office_info,
                    $search_condition_input_data,
                    $product
                );
            }

            // 合計
            $this->setDetailTotalData($active_sheet, $sub_total, $quantity_total, $purchase_sub_total, $purchase_quantity_total);
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
     * 商品名の設定
     *
     * @param Worksheet $sheet シート情報
     * @param $product_data
     * @return Worksheet
     */
    private function setProductName(Worksheet $sheet, $product_data): Worksheet
    {
        $row = $this->product_name_row;

        // 商品コード
        $cell = "F$row";
        $sheet->getStyle($cell)->getFont()->setSize(16);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $product_data->code_zerofill . ':');

        // 商品名
        $cell = "G$row";
        $sheet->getStyle($cell)->getFont()->setSize(16);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $product_data->name);

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
        $cell = "M$row";
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
        $cell = "O$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($cell, $page_no);

        return $sheet;
    }

    /**
     * 会社名の設定
     *
     * @param Worksheet $sheet シート情報
     * @param $office_info
     * @return Worksheet
     */
    private function setCompany(Worksheet $sheet, $office_info): Worksheet
    {
        foreach ($office_info as $office) {
            $row = $this->company_name_row;

            // 会社名
            $cell = "L$row";
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
        $cell = "B$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $start_date);

        $cell = "E$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $end_date);

        return $sheet;
    }

    /**
     * 明細行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param int $row_count 行数
     * @param $order_detail
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDetailData(Worksheet $sheet, int $row_count, $order_detail): Worksheet
    {
        $row = $this->detail_row + ($row_count * 2);

        // 1行目
        // 伝票日付
        $date = DateHelper::getFullShortJpDate($order_detail->order_date_slash);

        $cell = "A$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $date);

        // 取引先コード
        $cell = "B$row:C$row";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        if ($order_detail->order_kind === OrderType::SALES) {
            $sheet->setCellValue("B$row", $order_detail->customer_code_zero_fill);
        }
        if ($order_detail->order_kind === OrderType::PURCHASE) {
            $sheet->setCellValue("B$row", $order_detail->supplier_code_zero_fill);
        }
        if ($order_detail->order_kind !== OrderType::SALES && $order_detail->order_kind !== OrderType::PURCHASE) {
            $sheet->setCellValue("B$row", 'コード無し');
        }

        // 単位
        $cell = "D$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $order_detail->unit_name);

        // 商品名
        $cell = "I$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $order_detail->product_name);

        // 備考
        $cell = "N$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $order_detail->note);

        ++$row;

        // 2行目
        // 伝票番号
        $cell = "A$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $order_detail->order_number_zero_fill);

        // 種別
        $cell = "B$row:C$row";
        $sheet->mergeCells($cell);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue("B$row", OrderType::getDescription($order_detail->order_kind));

        // 取引先
        $cell = "D$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        if ($order_detail->order_kind === OrderType::SALES) {
            $sheet->setCellValue($cell, $order_detail->customer_name . '　' . $order_detail->branch_name ?? null);
        }
        if ($order_detail->order_kind === OrderType::PURCHASE) {
            $sheet->setCellValue($cell, $order_detail->supplier_name);
        }
        if ($order_detail->order_kind !== OrderType::SALES && $order_detail->order_kind !== OrderType::PURCHASE) {
            $sheet->setCellValue($cell, '取引先無し');
        }

        // 単価
        $cell = "I$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $formatUnitPrice = '#,##0';
        $unit_price_decimal_digit = $order_detail->unit_price_decimal_digit;
        if ($unit_price_decimal_digit > 0) {
            $formatUnitPrice = $formatUnitPrice . sprintf(".%0{$unit_price_decimal_digit}d", 0);
        }
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($formatUnitPrice);
        $sheet->setCellValue($cell, $order_detail->unit_price);

        if ($order_detail->order_kind === OrderType::SALES) {
            // 出庫
            $cell = "L$row";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
            $sheet->setCellValue($cell, $order_detail->quantity);
        }
        if ($order_detail->order_kind === OrderType::PURCHASE) {
            // 在庫
            $cell = "M$row";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
            $sheet->setCellValue($cell, $order_detail->quantity);
        }

        return $sheet;
    }

    /**
     * 明細合計行の設定
     *
     * @param Worksheet $sheet
     * @param $sub_total
     * @param $quantity_total
     * @param $purchase_sub_total
     * @param $purchase_quantity_total
     */
    private function setDetailTotalData(Worksheet $sheet, $sub_total, $quantity_total, $purchase_sub_total, $purchase_quantity_total): void
    {
        $row = $this->total_quantity_row;

        // 売上数量
        $cell = "D$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, '※　売上数量　※');

        $cell = "L$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $quantity_total);

        $row = $this->sub_total_row;

        // 売上合計
        $cell = "D$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, '※　売上合計　※');

        $cell = "L$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $sub_total);

        $row = $this->purchase_total_quantity_row;

        // 仕入数量
        $cell = "D$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, '※　仕入数量　※');

        $cell = "M$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $purchase_quantity_total);

        $row = $this->purchase_sub_total_row;

        // 仕入合計
        $cell = "D$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, '※　仕入合計　※');

        $cell = "M$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue($cell, $purchase_sub_total);
    }

    /**
     * 編集対象のシート取得
     *
     * @param Spreadsheet $spreadsheet
     * @param Worksheet $sheet
     * @param $page_no
     * @param $product
     * @return Worksheet|null
     *
     * @throws Exception
     */
    private function getActiveSheet(Spreadsheet $spreadsheet, Worksheet $sheet, $page_no, $product): ?Worksheet
    {
        // シートの複製
        $cloned_sheet = clone $sheet;

        // シート名
        $sheet_name = '商品台帳_' . $product->code_zerofill . '_' . $page_no;
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
     * @param $product
     * @return Worksheet
     */
    private function setPublicPageData(Worksheet $sheet, $page_no, $office_info, $search_condition_input_data, $product): Worksheet
    {
        // 商品名
        $sheet = $this->setProductName($sheet, $product);

        // 年月日
        $sheet = $this->setDate($sheet);

        // ページ番号
        $sheet = $this->setPageNo($sheet, $page_no);

        // 会社情報
        $sheet = $this->setCompany($sheet, $office_info);

        // 出力期間
        return $this->setOrderDate($sheet, $search_condition_input_data['order_date']);
    }
}
