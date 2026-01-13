<?php

/**
 * 種別累計売上表用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Sale\Ledger;

use App\Consts\SessionConst;
use App\Helpers\DateHelper;
use App\Helpers\PdfHelper;
use App\Http\Requests\Sale\Ledger\CategorySearchRequest;
use App\Models\Master\MasterCategory;
use App\Models\Master\MasterProduct;
use App\Repositories\Sale\Ledger\CategoryRepository;
use App\Services\Excel\PrintExcelCommonService;
use DateTime;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * 種別累計売上表用サービス
 */
class CategoryService
{
    use SessionConst;

    protected CategoryRepository $repository;

    /** タイトル */
    protected string $title_name = '種別累計売上表';

    /** 明細行の行位置 */
    protected int $detail_row;

    /** 各ブロックの開始位置 */
    protected int $title = 2;

    protected int $slip_date = 4;

    /**
     * リポジトリをインスタンス
     *
     * @param CategoryRepository $repository
     */
    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;

        // 明細行の行位置
        $this->detail_row = 7;
    }

    /**
     * 一覧画面
     *
     * @param array $input_data
     * @return array
     */
    public function index(array $input_data): array
    {
        return [
            /** 検索項目 */
            'search_items' => [
                'products' => MasterProduct::query()->oldest('name_kana')->get(),
                'categories' => MasterCategory::query()->oldest('code')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $input_data,
            /** 検索結果 */
            'search_result' => [
                'sales_orders' => $this->repository->getSearchResult($input_data),
                'category_total' => $this->repository->getDataCategoryTotal($input_data),
            ],
        ];
    }

    /**
     * PDF表示（Excel -> PDF変換）
     *
     * @param CategorySearchRequest $request
     * @return array
     *
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function showPdf(CategorySearchRequest $request): array
    {
        $spreadsheet = $this->getSpreadSheet($request);

        $user_id = Auth::user()->id_zerofill ?? '0000000000';
        $file_name = date('YmdHis');
        $excel_file_name = "{$file_name}_$user_id.xlsx";
        $pdf_file_name = "{$file_name}_$user_id.pdf";

        // 一旦、Excelファイルを保存
        $excel_path = storage_path(config('consts.excel.temp_path')) . $excel_file_name;
        (new Xlsx($spreadsheet))->save($excel_path);

        // Excel -> PDF 変換
        $pdf_dir = public_path(config('consts.pdf.temp_path'));

        // PDFファイルURLにリダイレクト
        return [$pdf_file_name, PdfHelper::convertPdf($excel_path, $pdf_dir)];
    }

    /**
     * Excelデータ作成
     *
     * @param CategorySearchRequest $request
     * @return Spreadsheet
     *
     * @throws Exception
     */
    public function getSpreadSheet(CategorySearchRequest $request): Spreadsheet
    {
        $search_condition_input_data = $request->validated();

        $path = storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.ledger_category')
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

        // 表示データ取得
        $search_result = $this->repository->getSearchResult($search_condition_input_data);

        // 編集対象のシート取得
        $active_sheet = $this->getActiveSheet($spreadsheet, $sheet);

        // ページ共通のデータを設定
        $active_sheet = $this->setPublicPageData(
            $active_sheet,
            $search_condition_input_data
        );

        // 合計
        $sub_total = $this->repository->getDataCategoryTotal($search_condition_input_data);

        foreach ($search_result as $detail_data) {
            // 明細行
            $active_sheet = $this->setDetailData($active_sheet, $detail_data);
        }

        // 合計
        $this->setDetailTotalData($active_sheet, $sub_total, $this->detail_row);
        $this->detail_row = ++$this->detail_row;

        $this->setLastBorder($active_sheet);

        if ($spreadsheet->getSheetCount() > 1) {
            // 先頭のシートを削除
            $spreadsheet->removeSheetByIndex(0);
        }

        // 先頭のシートをアクティブにする
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * 明細行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param object $order_detail
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDetailData(Worksheet $sheet, object $order_detail): Worksheet
    {
        $row = $this->detail_row;

        $arrStyle = PrintExcelCommonService::$arrStyleDottedThikDottedMEDIUM;
        $arrStyle2 = PrintExcelCommonService::$arrStyleDottedThinDottedThin;
        $arrStyle3 = PrintExcelCommonService::$arrStyleDottedThikDottedThin;

        // 伝票日付
        $date = DateHelper::getFullShortJpDate($order_detail->order_date);

        $cell = "A$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle);
        $sheet->setCellValue($cell, $date);

        // 肥料
        $cell = "B$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail->fertilizer);

        // 農薬
        $cell = "C$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail->pesticide);

        // 資材
        $cell = "D$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail->material);

        // 種子
        $cell = "E$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail->seed);

        // その他
        $cell = "F$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $order_detail->another);

        // 日計
        $cell = "G$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle3);
        $sheet->setCellValue($cell, $order_detail->day_total);

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
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->getStyle($cell)->applyFromArray($arrStyle);
        $sheet->setCellValue($cell, '※　合計　※');

        // 肥料
        $cell = "B$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total->fertilizer_total);

        // 農薬
        $cell = "C$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total->pesticide_total);

        // 資材
        $cell = "D$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total->material_total);

        // 種子
        $cell = "E$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total->seed_total);

        // その他
        $cell = "F$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle2);
        $sheet->setCellValue($cell, $category_total->another_total);

        // 日計
        $cell = "G$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($cell)->applyFromArray($arrStyle3);
        $sheet->setCellValue($cell, $category_total->all_total);
    }

    /**
     * 編集対象のシート取得
     *
     * @param Spreadsheet $spreadsheet
     * @param Worksheet $sheet
     * @return Worksheet|null
     */
    private function getActiveSheet(Spreadsheet $spreadsheet, Worksheet $sheet): ?Worksheet
    {
        // シートの複製
        $cloned_sheet = clone $sheet;

        // シート名
        $cloned_sheet->setTitle($this->title_name);

        // 編集対象のシート取得
        return $spreadsheet->getSheetByName($this->title_name);
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

        // タイトル
        $cell = "C$row";
        $sheet->getStyle($cell)->getFont()->setSize(18);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $this->title_name);

        $row = $this->slip_date;
        $order_date_start = $search_condition_input_data['order_date']['start'];
        $order_date_end = $search_condition_input_data['order_date']['end'];

        $cell = "A$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, (new DateTime($order_date_start))->format('Y/m/d'));

        $cell = "C$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, (new DateTime($order_date_end))->format('Y/m/d'));

        // 年月日
        $cell = "G$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, DateHelper::getFullJpDate(now()->format('Y-m-d')));

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
        $cell = "A$this->detail_row:G$this->detail_row";
        $sheet->getStyle($cell)->applyFromArray($arrStyle1);
    }
}
