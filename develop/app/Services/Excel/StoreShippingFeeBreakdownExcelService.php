<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterProduct;
use App\Models\Sale\SalesOrderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StoreShippingFeeBreakdownExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 3;

    /** 明細行の終わりの行 */
    protected int $last_row_detail;

    /** 配送会社コードと名前のマッピング */
    private array $shippingCompanies = [];

    /** 配送会社コード */
    private array $shippingCompanyCodes = [];

    public function __construct()
    {
        // 配送会社コードを取得
        $this->shippingCompanyCodes = MasterProduct::getSendFeeGroupProductCode();

        $this->shippingCompanies = [
            $this->shippingCompanyCodes[0] => '佐　川　急　便',
            $this->shippingCompanyCodes[1] => '郵便事業（株）',
            $this->shippingCompanyCodes[2] => '九州ヤマト運輸',
        ];

        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.store_shipping_fee_breakdown')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.store_shipping_fee_breakdown')
            . $prefix . config('consts.pdf.filename.file_extension');

        parent::__construct($downloadExcelFileName, $downloadPdfFileName);
    }

    /**
     * Excelデータ作成
     *
     * @param array $searchConditions
     * @param array $outputData
     * @param bool $isPdf
     * @return Spreadsheet
     *
     * @throws Exception
     */
    public function getSpreadSheet(array $searchConditions, array $outputData, bool $isPdf = false): Spreadsheet
    {
        // テンプレートファイルの読み込み
        $activeSheet = $this->initSpreadSheet(storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.store_shipping_fee_breakdown')
        ), PageSetup::ORIENTATION_LANDSCAPE);

        // ヘッダー行の出力
        $this->setHeader($activeSheet, $searchConditions);

        // タイトル行の出力
        $this->setTitle($activeSheet);

        // 明細行の出力
        $this->setDetails($activeSheet, $outputData);

        // 合計行の出力
        $this->setTotal($activeSheet, $outputData);

        // Excelシートの設定
        $this->setPageSetup($activeSheet);

        return $this->spreadSheet;
    }

    /**
     * ヘッダー行の出力
     *
     * @param Worksheet $activeSheet
     * @param array $searchConditions
     * @return void
     */
    private function setHeader(Worksheet $activeSheet, array $searchConditions): void
    {
        $startDate = $searchConditions['start_date'] ?? '';
        $monthDate = $startDate ? (new Carbon($startDate))->format('Y年m月') : now()->format('Y年m月');

        $headerText = "{$monthDate}分　＊＊ 各店送料内訳 ＊＊";
        $activeSheet->setCellValue('A1', $headerText);
        $activeSheet->mergeCells('A1:E1');
        $activeSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * タイトル行の出力
     *
     * @param Worksheet $activeSheet
     * @return void
     */
    private function setTitle(Worksheet $activeSheet): void
    {
        $activeSheet->setCellValue('A2', '店舗名');
        $activeSheet->setCellValue('B2', $this->shippingCompanies[$this->shippingCompanyCodes[2]]);
        $activeSheet->setCellValue('C2', $this->shippingCompanies[$this->shippingCompanyCodes[0]]);
        $activeSheet->setCellValue('D2', $this->shippingCompanies[$this->shippingCompanyCodes[1]]);
        $activeSheet->setCellValue('E2', '店　舗　合　計');

        // カラム幅の設定
        $activeSheet->getColumnDimension('A')->setWidth(20);
        $activeSheet->getColumnDimension('B')->setWidth(15);
        $activeSheet->getColumnDimension('C')->setWidth(15);
        $activeSheet->getColumnDimension('D')->setWidth(15);
        $activeSheet->getColumnDimension('E')->setWidth(15);

        // スタイルの設定
        $activeSheet->getStyle('A2:E2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $activeSheet->getStyle('A2:E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * 明細データの出力
     *
     * @param Worksheet $activeSheet
     * @param array $outputData DBから取得した明細データ
     * @return void
     */
    private function setDetails(Worksheet $activeSheet, array $outputData): void
    {
        $storeData = [];
        foreach ($outputData as $data) {
            $store = $data['name'];
            $code = $data['code'];
            $amount = $data['total_sub_total'];

            if (!isset($storeData[$store])) {
                $storeData[$store] = [
                    $this->shippingCompanyCodes[0] => 0,
                    $this->shippingCompanyCodes[1] => 0,
                    $this->shippingCompanyCodes[2] => 0,
                    'total' => 0,
                ];
            }

            $storeData[$store][$code] = $amount;
            $storeData[$store]['total'] += $amount;
        }

        $row = $this->start_row_detail;

        foreach ($storeData as $store => $amounts) {
            $activeSheet->setCellValue("A{$row}", $store);
            $activeSheet->setCellValue("B{$row}", $amounts[$this->shippingCompanyCodes[2]] ?: 0);
            $activeSheet->setCellValue("C{$row}", $amounts[$this->shippingCompanyCodes[0]] ?: 0);
            $activeSheet->setCellValue("D{$row}", $amounts[$this->shippingCompanyCodes[1]] ?: 0);
            $activeSheet->setCellValue("E{$row}", $amounts['total']);

            // スタイルの設定
            $activeSheet->getStyle("B{$row}:E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $activeSheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            ++$row;
        }

        // 行番号をsetToatalに渡すためメンバ変数で保持
        $this->last_row_detail = $row;
    }

    /**
     * 合計行の出力
     *
     * @param Worksheet $activeSheet
     * @param array $outputData
     * @return void
     */
    private function setTotal(Worksheet $activeSheet, array $outputData): void
    {
        $colTotals = [
            $this->shippingCompanyCodes[0] => 0,
            $this->shippingCompanyCodes[1] => 0,
            $this->shippingCompanyCodes[2] => 0,
            'total' => 0,
        ];

        foreach ($outputData as $data) {
            $code = $data['code'];
            $amount = $data['total_sub_total'];

            $colTotals[$code] += $amount;
            $colTotals['total'] += $amount;
        }

        $row = $this->last_row_detail;

        $activeSheet->setCellValue("A{$row}", '☆　合　計　☆');
        $activeSheet->setCellValue("B{$row}", $colTotals[$this->shippingCompanyCodes[2]]);
        $activeSheet->setCellValue("C{$row}", $colTotals[$this->shippingCompanyCodes[0]]);
        $activeSheet->setCellValue("D{$row}", $colTotals[$this->shippingCompanyCodes[1]]);
        $activeSheet->setCellValue("E{$row}", $colTotals['total']);

        // スタイルの設定
        $activeSheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $activeSheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $activeSheet->getStyle("B{$row}:E{$row}")->getNumberFormat()->setFormatCode('#,##0');
    }

    /**
     * Excelシートの設定
     *
     * @param Worksheet $activeSheet
     * @return void
     */
    private function setPageSetup(Worksheet $activeSheet): void
    {
        $activeSheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $activeSheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $activeSheet->getPageSetup()->setFitToWidth(1);
        $activeSheet->getPageSetup()->setFitToHeight(0);
    }

    /**
     * 帳票出力データを取得
     *
     * @param array $searchConditions
     * @return array
     */
    public function getOutputData(array $searchConditions): array
    {
        // 出力データの取得
        return SalesOrderDetail::query()
            ->select([
                'm_products.code',
                'm_office_facilities.name',
                DB::raw('SUM(sales_order_details.sub_total + sales_order_details.sub_total_tax) as total_sub_total'),
            ])
            ->leftJoin('sales_orders', 'sales_orders.id', '=', 'sales_order_details.sales_order_id')
            ->leftJoin('m_products', 'm_products.id', '=', 'sales_order_details.product_id')
            ->leftJoin('m_office_facilities', 'm_office_facilities.id', '=', 'sales_orders.office_facilities_id')
            // 対象商品だけに絞り込み
            ->whereIn('m_products.code', MasterProduct::getSendFeeGroupProductCode())
            // 部門で絞り込み
            ->where('sales_orders.department_id', MasterDepartment::getRetailId())
            // 伝票日付で絞り込み
            ->when($searchConditions !== null, function ($query) use ($searchConditions) {
                if (is_null($searchConditions['start_date']) && is_null($searchConditions['end_date'])) {
                    return $query;
                }
                if (isset($searchConditions['start_date']) && is_null($searchConditions['end_date'])) {
                    return $query->where('sales_orders.order_date', '>=', $searchConditions['start_date']);
                }
                if (is_null($searchConditions['start_date']) && isset($searchConditions['end_date'])) {
                    return $query->where('sales_orders.order_date', '<=', $searchConditions['end_date']);
                }

                return $query->whereBetween('sales_orders.order_date', [$searchConditions['start_date'], $searchConditions['end_date']]);
            })
            ->groupBy('sales_orders.office_facilities_id', 'm_office_facilities.name', 'm_products.code')
            ->orderBy('sales_orders.office_facilities_id')
            ->orderBy('m_office_facilities.name')
            ->orderBy('m_products.code')
            ->get()
            ->toArray();
    }
}
