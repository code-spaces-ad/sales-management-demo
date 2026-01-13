<?php

/**
 * 年度別販売実績表用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Sale\Ledger;

use App\Consts\SessionConst;
use App\Enums\AggregationType;
use App\Helpers\DateHelper;
use App\Helpers\LedgerFiscalYearHelper;
use App\Helpers\PdfHelper;
use App\Http\Requests\Sale\Ledger\FiscalYearRequest;
use App\Models\Master\MasterHeadOfficeInfo;
use App\Models\Sale\SalesOrder;
use App\Repositories\Sale\Ledger\FiscalYearRepository;
use App\Services\Excel\PrintExcelCommonService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * 年度別販売実績表用サービス
 */
class FiscalYearService
{
    use SessionConst;

    protected FiscalYearRepository $repository;

    /** 明細行の行位置 */
    protected int $detail_row;

    /** 各ブロックの開始位置 */
    protected int $title = 2;

    protected int $slip_date = 4;

    /** 月のソート順 */
    protected array $month_sort_list;

    /** 伝票月名の行位置 */
    protected int $title_month_row;

    /**
     * リポジトリをインスタンス
     *
     * @param FiscalYearRepository $repository
     */
    public function __construct(FiscalYearRepository $repository)
    {
        $this->repository = $repository;

        // 明細行の行位置
        $this->detail_row = 7;

        // 伝票月名の行位置
        $this->title_month_row = 6;

        $this->month_sort_list = [];
    }

    /**
     * 一覧画面
     *
     * @param array $input_data
     * @return array
     */
    public function index(array $input_data): array
    {
        if ($input_data['fiscal_year']) {
            // 会計年度の範囲取得
            [$input_data['order_date']['start'], $input_data['order_date']['end']] =
                DateHelper::getFiscalYearRange($input_data['fiscal_year']);
        }

        return [
            /** 検索項目 */
            'search_items' => [
                /** 売上日付を元にした年度リスト */
                'fiscal_year' => DateHelper::getFiscalYearsListByOldestAndLatestDate(
                    SalesOrder::getTheOldestDate(),
                    SalesOrder::getTheLatestDate()
                ),
                /** 集計種別 */
                'aggregation_types' => AggregationType::asSelectArray(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $input_data,
            /** 検索結果 */
            'search_result' => [
                'sales_orders' => $this->repository->getSomeMonthTotal($input_data),
                'fiscal_total' => $this->repository->getSomeTotal($input_data),
            ],
        ];
    }

    /**
     * PDF表示（Excel -> PDF変換）
     *
     * @param FiscalYearRequest $request
     * @return array
     *
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function showPdf(FiscalYearRequest $request): array
    {
        $spreadsheet = $this->getSpreadSheet($request);

        $user_id = Auth::user()->id_zerofill ?? '0000000000';
        $file_name = date('YmdHis');
        $excel_file_name = "{$file_name}_$user_id.xlsx";
        $pdf_file_name = "{$file_name}_$user_id.pdf";

        // 一旦、Excelファイルを保存
        $excel_path = storage_path(config('consts.excel.temp_path')) . $excel_file_name;
        (new Xlsx($spreadsheet))->save($excel_path);

        // PDFファイルURLにリダイレクト
        return [$pdf_file_name, PdfHelper::convertPdf($excel_path, public_path(config('consts.pdf.temp_path')))];
    }

    /**
     * Excelデータ作成
     *
     * @param FiscalYearRequest $request
     * @return Spreadsheet
     *
     * @throws Exception
     */
    public function getSpreadSheet(FiscalYearRequest $request): Spreadsheet
    {
        $search_condition_input_data = $request->validated();

        $path = storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.ledger_fiscal_year')
        );
        $spreadsheet = IOFactory::load($path);

        // 期首から期末までの月をソートする
        $fiscal_year = MasterHeadOfficeInfo::query()->value('fiscal_year');
        $this->createMonthSortList($fiscal_year);

        // 表示データ取得
        $search_result = $this->repository->getProSearchResult($search_condition_input_data);

        // ページ番号
        $sheet_number = 0;

        // 編集対象のシート取得
        $active_sheet = $this->getActiveSheet($spreadsheet, $sheet_number);

        // ページ共通のデータを設定
        $active_sheet = $this->setPublicPageData(
            $active_sheet,
            $search_condition_input_data
        );

        // 合計
        $category_id = null;
        $sub_total = $this->repository->getDataMonthTotal($search_condition_input_data, $category_id)[0];

        foreach ($search_result as $detail_data) {
            // 明細行
            $master_lists = $this->repository->mCategory($detail_data->category_id);
            $active_sheet = $this->setDetailData($active_sheet, $detail_data, $master_lists);
        }

        // 合計
        $this->setDetailTotalData($active_sheet, $sub_total, $this->detail_row);
        $this->detail_row = ++$this->detail_row;

        $this->setLastBorder($active_sheet);

        // 表示データ取得(得意先)
        $search_result = $this->repository->getCustomerSearchResult($search_condition_input_data);

        // ページ番号
        $sheet_number = 1;

        // 編集対象のシート取得(得意先)
        $active_sheet = $this->getActiveSheet($spreadsheet, $sheet_number);

        // ページ共通のデータを設定(得意先)
        $active_sheet = $this->setPublicPageData(
            $active_sheet,
            $search_condition_input_data
        );

        $this->detail_row = 7;
        $this->title_month_row = 6;
        foreach ($search_result as $detail_data) {
            // 明細行(得意先)
            $master_lists = $this->repository->mCustomer($detail_data->id);
            $active_sheet = $this->setDetailData($active_sheet, $detail_data, $master_lists);
        }

        $this->setLastBorder($active_sheet);

        // 表示データ取得(バイオノ有機)
        $search_result = $this->repository->getBioSearchResult($search_condition_input_data);

        // ページ番号
        $sheet_number = 2;

        // 編集対象のシート取得(得意先)
        $active_sheet = $this->getActiveSheet($spreadsheet, $sheet_number);

        // ページ共通のデータを設定(バイオノ有機)
        $active_sheet = $this->setPublicPageData(
            $active_sheet,
            $search_condition_input_data
        );

        // 合計
        $category_id = 4;
        $sub_total = $this->repository->getDataMonthTotal($search_condition_input_data, $category_id)[0];

        $this->detail_row = 7;
        $this->title_month_row = 6;
        foreach ($search_result as $detail_data) {
            // 明細行(バイオノ有機)
            $master_lists = $this->repository->mCustomer($detail_data->customer_id);
            $active_sheet = $this->setDetailData($active_sheet, $detail_data, $master_lists);
        }

        // 合計
        $this->setDetailTotalData($active_sheet, $sub_total, $this->detail_row);
        $this->detail_row = ++$this->detail_row;

        $this->setLastBorder($active_sheet);

        // 表示データ取得(エキタン有機)
        $search_result = $this->repository->getEquitanSearchResult($search_condition_input_data);

        // ページ番号
        $sheet_number = 3;

        // 編集対象のシート取得(得意先)
        $active_sheet = $this->getActiveSheet($spreadsheet, $sheet_number);

        // ページ共通のデータを設定(エキタン有機)
        $active_sheet = $this->setPublicPageData(
            $active_sheet,
            $search_condition_input_data
        );

        // 合計
        $category_id = 3;
        $sub_total = $this->repository->getDataMonthTotal($search_condition_input_data, $category_id)[0];

        $this->detail_row = 7;
        $this->title_month_row = 6;
        foreach ($search_result as $detail_data) {
            // 明細行(エキタン有機)
            $master_lists = $this->repository->mCustomer($detail_data->customer_id);
            $active_sheet = $this->setDetailData($active_sheet, $detail_data, $master_lists);
        }

        // 合計
        $this->setDetailTotalData($active_sheet, $sub_total, $this->detail_row);
        $this->detail_row = ++$this->detail_row;

        $this->setLastBorder($active_sheet);

        // 表示データ取得(肥料)
        $search_result = $this->repository->getFertilizerSearchResult($search_condition_input_data);

        // ページ番号
        $sheet_number = 4;

        // 編集対象のシート取得(肥料)
        $active_sheet = $this->getActiveSheet($spreadsheet, $sheet_number);

        // ページ共通のデータを設定(肥料)
        $active_sheet = $this->setPublicPageData(
            $active_sheet,
            $search_condition_input_data
        );

        // 合計
        $category_id = 1;
        $sub_total = $this->repository->getDataMonthTotal($search_condition_input_data, $category_id)[0];

        $this->detail_row = 7;
        $this->title_month_row = 6;
        foreach ($search_result as $detail_data) {
            // 明細行(肥料)
            $master_lists = $this->repository->mProduct($detail_data->id);
            $active_sheet = $this->setDetailData($active_sheet, $detail_data, $master_lists);
        }

        // 合計
        $this->setDetailTotalData($active_sheet, $sub_total, $this->detail_row);
        $this->detail_row = ++$this->detail_row;

        $this->setLastBorder($active_sheet);

        // 表示データ取得(ストリームライン・タイフーン)
        $search_result = $this->repository->setSelectColumn($search_condition_input_data)->getStreamSearchResult($search_condition_input_data);

        // ページ番号
        $sheet_number = 5;

        // 編集対象のシート取得(ストリームライン・タイフーン)
        $active_sheet = $this->getActiveSheet($spreadsheet, $sheet_number);

        // ページ共通のデータを設定(ストリームライン・タイフーン)
        $active_sheet = $this->setPublicStreamPageData(
            $active_sheet,
            $search_condition_input_data
        );

        // 合計
        $sub_total = $this->repository->getDataCategoryTotal($search_condition_input_data)[0];

        $this->detail_row = 7;
        foreach ($search_result as $detail_data) {
            // 明細行(ストリームライン・タイフーン)
            $active_sheet = $this->setStreamDetailData($active_sheet, $detail_data);
        }

        // 合計
        $this->setStreamTotalData($active_sheet, $sub_total, $this->detail_row);
        $this->detail_row = ++$this->detail_row;

        $this->setStreamLastBorder($active_sheet);

        // 先頭のシートをアクティブにする
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * 明細行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param object $order_detail
     * @param object $master_lists
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDetailData(Worksheet $sheet, object $order_detail, object $master_lists): Worksheet
    {
        $row = $this->detail_row;
        $arrStyle = PrintExcelCommonService::$arrStyleDottedThikDottedMEDIUM;
        $arrStyle2 = PrintExcelCommonService::$arrStyleDottedThinDottedThin;
        $arrStyle3 = PrintExcelCommonService::$arrStyleDottedThikDottedThin;

        $cell = "A$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle);
        $sheet->setCellValue($cell, $master_lists->name);

        // 伝票月
        $cell = "B$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[0]]);

        $cell = "C$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[1]]);

        $cell = "D$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[2]]);

        $cell = "E$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[3]]);

        $cell = "F$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[4]]);

        $cell = "G$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[5]]);

        $cell = "H$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[6]]);

        $cell = "I$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[7]]);

        $cell = "J$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[8]]);

        $cell = "K$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[9]]);

        $cell = "L$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[10]]);

        $cell = "M$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail[$this->month_sort_list[11]]);

        // 年計
        $cell = "N$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle3);
        $sheet->setCellValue($cell, $order_detail->year_total);

        ++$this->detail_row;

        return $sheet;
    }

    /**
     * 明細行の設定(ストリーム・タイフーン)
     *
     * @param Worksheet $sheet シート情報
     * @param object $order_detail
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setStreamDetailData(Worksheet $sheet, object $order_detail): Worksheet
    {
        $customer = $this->repository->mCustomer($order_detail->customer_id);
        $products = $this->repository->mProduct($order_detail->id);
        $row = $this->detail_row;

        $arrStyle = PrintExcelCommonService::$arrStyleDottedThikDottedMEDIUM;
        $arrStyle2 = PrintExcelCommonService::$arrStyleDottedThinDottedThin;
        $arrStyle3 = PrintExcelCommonService::$arrStyleDottedThikDottedThin;

        // 商品名
        $cell = "A$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle);
        $sheet->setCellValue($cell, $products->name);

        // 得意先名
        $cell = "B$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $customer->name);

        // 年計
        $cell = "C$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle3);
        $sheet->setCellValue($cell, $order_detail->year_total);

        ++$this->detail_row;

        return $sheet;
    }

    /**
     * 明細合計行の設定
     *
     * @param Worksheet $sheet
     * @param object $category_total
     * @param int $row
     *
     * @throws Exception
     */
    private function setDetailTotalData(Worksheet $sheet, object $category_total, int $row): void
    {
        $arrStyle = PrintExcelCommonService::$arrStyleDottedThikDottedMEDIUM;
        $arrStyle2 = PrintExcelCommonService::$arrStyleDottedThinDottedThin;
        $arrStyle3 = PrintExcelCommonService::$arrStyleDottedThikDottedThin;

        // 合計
        $cell = "A$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle);
        $sheet->setCellValue($cell, '合計');

        $cell = "B$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[0]]);

        $cell = "C$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[1]]);

        $cell = "D$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[2]]);

        $cell = "E$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[3]]);

        $cell = "F$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[4]]);

        $cell = "G$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[5]]);

        $cell = "H$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[6]]);

        $cell = "I$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[7]]);

        $cell = "J$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[8]]);

        $cell = "K$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[9]]);

        $cell = "L$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[10]]);

        $cell = "M$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total[$this->month_sort_list[11]]);

        $cell = "N$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle3);
        $sheet->setCellValue($cell, $category_total->year_total);
    }

    /**
     * 明細合計行の設定(ストリーム・タイフーン)
     *
     * @param Worksheet $sheet
     * @param object $category_total
     * @param int $row
     *
     * @throws Exception
     */
    private function setStreamTotalData(Worksheet $sheet, object $category_total, int $row): void
    {
        $arrStyle = PrintExcelCommonService::$arrStyleDottedThikDottedMEDIUM;
        $arrStyle2 = PrintExcelCommonService::$arrStyleDottedThinDottedThin;
        $arrStyle3 = PrintExcelCommonService::$arrStyleDottedThikDottedThin;

        // 合計
        $cell = "A$row";
        $sheet->getStyle($cell)->applyFromArray($arrStyle);

        $cell = "B$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, '　合計　');

        $cell = "C$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle3);
        $sheet->setCellValue($cell, $category_total->Stream_total);
    }

    /**
     * 編集対象のシート取得
     *
     * @param Spreadsheet $spreadsheet
     * @param int $sheet_number
     * @return Worksheet|null
     *
     * @throws Exception
     */
    private function getActiveSheet(Spreadsheet $spreadsheet, int $sheet_number): ?Worksheet
    {
        // 編集対象のシート取得
        return $spreadsheet->getSheet($sheet_number);
    }

    /**
     * ページ共通の設定値を設定
     *
     * @param Worksheet $sheet
     * @param array $search_condition_input_data
     * @return Worksheet
     *
     * @throws \Exception
     */
    private function setPublicPageData(Worksheet $sheet, array $search_condition_input_data): Worksheet
    {
        $row = $this->title;
        $title_value = $sheet->getTitle();

        // タイトル
        $cell = "G$row";
        $sheet->getStyle($cell)->getFont()->setSize(18);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $title_value);

        $row = $this->slip_date;
        $order_date_start = $search_condition_input_data['order_date']['start'];

        $cell = "A$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, (new DateTime($order_date_start))->format('Y年度'));

        $date = new Carbon('now');
        $now_date = DateHelper::getFullJpDate($date->format('Y-m-d'));

        // 年月日
        $cell = "N$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $now_date);

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

        return $sheet;
    }

    /**
     * ページ共通の設定値を設定(ストリームライン・タイフーン)
     *
     * @param Worksheet $sheet
     * @param array $search_condition_input_data
     * @return Worksheet
     *
     * @throws \Exception
     */
    private function setPublicStreamPageData(Worksheet $sheet, array $search_condition_input_data): Worksheet
    {
        $row = $this->title;
        $title_value = $sheet->getTitle();

        // タイトル
        $cell = "A$row";
        $sheet->getStyle($cell)->getFont()->setSize(18);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $title_value);

        $row = $this->slip_date;
        $order_date_start = $search_condition_input_data['order_date']['start'];

        $cell = "A$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, (new DateTime($order_date_start))->format('Y年度'));

        $date = new Carbon('now');
        $now_date = DateHelper::getFullJpDate($date->format('Y-m-d'));

        // 年月日
        $cell = "C$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $now_date);

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

        return $sheet;
    }

    /**
     * 最終行の罫線を設定
     *
     * @param Worksheet $sheet
     * @return void
     *
     * @throws Exception
     */
    private function setLastBorder(Worksheet $sheet): void
    {
        $arrStyle1 = PrintExcelCommonService::$arrStyleBORDER_THICK;
        $cell = "A$this->detail_row:N$this->detail_row";
        $sheet->getStyle($cell)->applyFromArray($arrStyle1);
    }

    /**
     * 最終行の罫線を設定
     *
     * @param Worksheet $sheet
     * @return void
     *
     * @throws Exception
     */
    private function setStreamLastBorder(Worksheet $sheet): void
    {
        $arrStyle1 = PrintExcelCommonService::$arrStyleBORDER_THICK;
        $cell = "A$this->detail_row:C$this->detail_row";
        $sheet->getStyle($cell)->applyFromArray($arrStyle1);
    }

    /**
     * 期首から期末までの月をソートする
     *
     * @param $fiscal_year
     * @return void
     */
    private function createMonthSortList($fiscal_year)
    {
        for ($month = $fiscal_year; $month <= 12; ++$month) {
            $this->month_sort_list[] = LedgerFiscalYearHelper::getNameByMonth($month);
        }
        for ($month = 1; $month < $fiscal_year; ++$month) {
            $this->month_sort_list[] = LedgerFiscalYearHelper::getNameByMonth($month);
        }
    }
}
