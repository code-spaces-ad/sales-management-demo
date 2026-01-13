<?php

namespace App\Services\Excel;

use App\Helpers\DateHelper;
use App\Http\Requests\Receive\OrdersReceivedSearchRequest;
use App\Repositories\Receive\OrdersReceivedRepository;
use Carbon\Carbon;
use DateTime;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderReceivedExcelService
{
    /** 各ブロックの開始位置 */
    protected $title = 2;

    protected $slip_date = 4;

    protected $now_date_row = 4;

    protected $detail_row = 9;

    /** 受注伝票リポジトリ */
    protected OrdersReceivedRepository $order_received_repository;

    /**
     * リポジトリをインスタンス
     *
     * @param OrdersReceivedRepository $order_received_repository
     */
    public function __construct(OrdersReceivedRepository $order_received_repository)
    {
        $this->order_received_repository = $order_received_repository;
    }

    /**
     * Excelデータ作成
     *
     * @param OrdersReceivedSearchRequest $request
     * @return Spreadsheet
     *
     * @throws Exception
     */
    public function getSpreadSheet(OrdersReceivedSearchRequest $request): Spreadsheet
    {
        $search_condition_input_data = $request->validated();
        // 発注日期間のデフォルトセット
        if (!isset($search_condition_input_data['order_date'])) {
            /** 発注日（開始）：月初 */
            $search_condition_input_data['order_date']['start'] = Carbon::now()->startOfMonth()->toDateString();
            /** 発注日（終了）：月末 */
            $search_condition_input_data['order_date']['end'] = Carbon::now()->endOfMonth()->toDateString();
        }

        // テンプレートファイルの読み込み
        $path = storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.orders_received')
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

        $orders_received = $this->order_received_repository
            ->getOrderReceivedResult($search_condition_input_data, 'asc');

        // 編集対象のシート取得
        $active_sheet = $this->getActiveSheet($spreadsheet, $sheet);

        $active_sheet = $this->setPublicPageData(
            $active_sheet,
            $search_condition_input_data
        );

        foreach ($orders_received as $orders) {
            $active_sheet = $this->setDetailData($active_sheet, $orders);
        }

        $active_sheet = $this->setLastBorder($active_sheet);

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
     * @param Worksheet $sheet
     * @return Worksheet|null
     *
     * @throws Exception
     */
    private function getActiveSheet(Spreadsheet $spreadsheet, Worksheet $sheet): ?Worksheet
    {
        // シートの複製
        $cloned_sheet = clone $sheet;

        // シート名
        $sheet_name = '受注伝票一覧';
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
     * @param array $search_condition_input_data
     * @return Worksheet
     *
     * @throws \Exception
     */
    private function setPublicPageData(Worksheet $sheet, array $search_condition_input_data): Worksheet
    {
        // 得意先名
        return $this->setPageHeaderInfo($sheet, $search_condition_input_data);
    }

    /**
     * 年月日の設定
     *
     * @param Worksheet $sheet シート情報
     * @param array $search_condition_input_data
     * @return Worksheet
     *
     * @throws \Exception
     */
    private function setPageHeaderInfo(Worksheet $sheet, array $search_condition_input_data): Worksheet
    {
        $row = $this->title;
        $title_value = '受注一覧表';

        // タイトル
        $cell = "H$row";
        $sheet->getStyle($cell)->getFont()->setSize(18);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, $title_value);

        $row = $this->slip_date;
        $order_date_start = $search_condition_input_data['order_date']['start'];
        $order_date_end = $search_condition_input_data['order_date']['end'];

        $cell = "B$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, (new DateTime($order_date_start))->format('Y/m/d'));

        $cell = "D$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->setCellValue($cell, (new DateTime($order_date_end))->format('Y/m/d'));

        $row = $this->now_date_row;
        $date = new Carbon('now');
        $now_date = DateHelper::getFullJpDate($date->format('Y-m-d'));

        // 年月日
        $cell = "R$row";
        $sheet->getStyle($cell)->getFont()->setSize(11);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->setCellValue($cell, $now_date);

        return $sheet;
    }

    private function setLastBorder(Worksheet $sheet): Worksheet
    {
        $arrStyle1 = PrintExcelCommonService::$arrStyleBORDER_THICK;
        $cell = "A$this->detail_row:U$this->detail_row";
        $sheet->getStyle($cell)->applyFromArray($arrStyle1);

        return $sheet;
    }

    /**
     * 明細行の設定
     *
     * @param Worksheet $sheet シート情報
     * @param object $order_received 伝票情報
     * @return Worksheet
     *
     * @throws Exception
     */
    private function setDetailData(Worksheet $sheet, object $order_received): Worksheet
    {
        $row = $this->detail_row;

        $arrStyle = PrintExcelCommonService::$arrStyleDottedThinDottedThin;
        $arrStyle2 = PrintExcelCommonService::$arrStyleDottedThikDottedThin;

        // 受注日
        $cell = "A$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle($cell)->applyFromArray($arrStyle);
        $sheet->setCellValue($cell, $order_received->order_date_slash);

        // 受注番号
        $cell = "B$row";
        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
        $sheet->getStyle($cell)->applyFromArray($arrStyle);
        $sheet->setCellValue($cell, $order_received->order_number_zerofill);

        // 得意先
        $cell = "C$row";
        $cells = "$cell:D$row";
        $sheet->mergeCells($cells);
        $sheet->getStyle($cells)->getAlignment()->setHorizontal('left');
        $sheet->getStyle($cells)->applyFromArray($arrStyle);
        $sheet->setCellValue($cell, $order_received->customer_name);

        // 支所名
        $cell = "E$row";
        $cells = "$cell:F$row";
        $sheet->mergeCells($cells);
        $sheet->getStyle($cells)->getAlignment()->setHorizontal('left');
        $sheet->getStyle($cells)->applyFromArray($arrStyle);
        $sheet->setCellValue($cell, $order_received->branch_name);

        // 納品先
        $cell = "G$row";
        $cells = "$cell:H$row";
        $sheet->mergeCells($cells);
        $sheet->getStyle($cells)->getAlignment()->setHorizontal('left');
        $sheet->getStyle($cells)->applyFromArray($arrStyle);
        $sheet->setCellValue($cell, $order_received->recipient_name);

        foreach ($order_received->ordersReceivedDetail as $ordersReceivedDetail) {
            $cell = "A$row";
            $sheet->getStyle($cell)->applyFromArray($arrStyle);
            $cell = "B$row";
            $sheet->getStyle($cell)->applyFromArray($arrStyle);
            $cell = "C$row:D$row";
            $sheet->getStyle($cell)->applyFromArray($arrStyle);
            $cell = "E$row:F$row";
            $sheet->getStyle($cell)->applyFromArray($arrStyle);
            $cell = "G$row:H$row";
            $sheet->getStyle($cell)->applyFromArray($arrStyle);

            // 商品名
            $cell = "I$row";
            $cells = "$cell:L$row";
            $sheet->mergeCells($cells);
            $sheet->getStyle($cells)->getAlignment()->setHorizontal('left');
            $sheet->getStyle($cells)->applyFromArray($arrStyle);
            $sheet->setCellValue($cell, $ordersReceivedDetail->product_name);

            // 数量
            $cell = "M$row";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
            $sheet->getStyle($cell)->applyFromArray($arrStyle);
            $sheet->setCellValue($cell, $ordersReceivedDetail->quantity);

            // 納品日
            $cell = "N$row";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('right');
            $sheet->getStyle($cell)->applyFromArray($arrStyle);
            $sheet->setCellValue($cell, (new DateTime($ordersReceivedDetail->delivery_date))->format('Y/m/d'));

            // 倉庫
            $cell = "O$row";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
            $sheet->getStyle($cell)->applyFromArray($arrStyle);
            $sheet->setCellValue($cell, $ordersReceivedDetail->warehouse_name);

            // 売上
            $cell = "P$row";
            $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
            $sheet->getStyle($cell)->applyFromArray($arrStyle);
            $sheet->setCellValue($cell, $ordersReceivedDetail->sales_confirm);

            // 備考
            $cell = "Q$row";
            $cells = "$cell:R$row";
            $sheet->mergeCells($cells);
            $sheet->getStyle($cells)->getAlignment()->setHorizontal('left');
            $sheet->getStyle($cells)->applyFromArray($arrStyle2);
            $sheet->setCellValue($cell, $ordersReceivedDetail->note);

            ++$row;
        }
        $this->detail_row = $row;

        return $sheet;
    }
}
