<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Enums\TransactionType;
use App\Helpers\SettingsHelper;
use App\Helpers\StringHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BankTransferFeeExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 8;

    /** 明細行の最大行 */
    protected int $max_row_detail = 18;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.bank_transfer_fee')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.bank_transfer_fee')
            . $prefix . config('consts.pdf.filename.file_extension');

        parent::__construct($downloadExcelFileName, $downloadPdfFileName);
    }

    /**
     * 有効なデータがあるか確認する
     *
     * @param array $outputData
     * @return bool
     */
    public function hasValidData(array $outputData): bool
    {
        if (empty($outputData)) {
            return false;
        }

        foreach ($outputData as $data) {
            // 注文数が0より大きい場合は有効なデータがある
            if (isset($data->order_count) && $data->order_count > 0) {
                return true;
            }
            // 入金合計に値がある場合は有効なデータがある
            if (isset($data->total_deposit) && $data->total_deposit !== null && $data->total_deposit != '0') {
                return true;
            }

            // 手数料合計に値がある場合は有効なデータがある
            if (isset($data->total_amount_fee) && $data->total_amount_fee !== null && $data->total_amount_fee != '0') {
                return true;
            }
        }

        // すべてのデータが0の場合は、有効なデータがないと判断
        return false;
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
        $sheet = $this->initSpreadSheet(storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.bank_transfer_fee')
        ), PageSetup::ORIENTATION_LANDSCAPE);

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

        // タイトル
        $title = (new Carbon($searchConditions['start_date'])->format('Y.m.d')) . ' 〜 ' . (new Carbon($searchConditions['end_date'])->format('Y.m.d'));
        $sheet->setCellValue('A3', '振込手数料明細　' . $title);

        //　データ部
        $add_row = 0;
        $current_col = 2;
        $count = 1;
        foreach ($outputData as $data) {
            if ($count === 5) {
                $add_row += 5;
                $current_col = 2;
                $count = 1;

                // セル結合
                $mergeRange = 'A' . (4 + $add_row) . ':A' . (6 + $add_row);
                $sheet->mergeCells($mergeRange);

                $targetRange = 'A' . (4 + $add_row) . ':E' . (8 + $add_row);
                // フォントの設定
                $sheet->getStyle($targetRange)->getFont()->setName('ＭＳ Ｐ明朝');
                $sheet->getStyle($targetRange)->getFont()->setSize(20);
                $sheet->getStyle($targetRange)->getFont()->setBold(true); // 太字
                $sheet->getStyle($targetRange)->getFont()->setItalic(false); // 斜体
                // 罫線の設定
                $sheet->getStyle($targetRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // 事業所名：配置の設定
                $targetRange = 'A' . (5 + $add_row) . ':E' . (5 + $add_row);
                $sheet->getStyle($targetRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($targetRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($targetRange)->getAlignment()->setShrinkToFit(true);
                // 金額部：配置の設定
                $targetRange = 'A' . (7 + $add_row) . ':E' . (8 + $add_row);
                $sheet->getStyle($targetRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle($targetRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                // 固定文字列
                $targetRange = 'A' . (7 + $add_row);
                $sheet->setCellValue($targetRange, '振込手数料');
                $targetRange = 'A' . (8 + $add_row);
                $sheet->setCellValue($targetRange, '総入金額');
            }
            $col = Coordinate::stringFromColumnIndex($current_col);
            // 事業所名
            $current_row = 5 + $add_row;
            $sheet->setCellValue("{$col}{$current_row}",
                StringHelper::replaceToBlank($data->office_facility_name, SettingsHelper::getReportBankTransferFeeReplaceToBlank()));
            // 振込手数料
            $current_row += 2;
            $sheet->setCellValue("{$col}{$current_row}", $data->total_amount_fee ?? 0);
            // 総入金額
            $current_row += 1;
            $sheet->setCellValue("{$col}{$current_row}", $data->total_deposit ?? 0);

            ++$current_col;
            ++$count;
        }

        //　高さ調整
        foreach ($sheet->getRowIterator() as $row) {
            if ($row->getRowIndex() === 2) {
                continue; // 2行目だけスキップ
            }
            $sheet->getRowDimension($row->getRowIndex())->setRowHeight(24);
        }

        //　初期セル位置をA1にセット
        $sheet->setSelectedCell('A1');

        return $this->spreadSheet;
    }

    /**
     * 帳票出力データを取得
     *
     * @param array $searchConditions
     * @return array
     */
    public function getOutputData(array $searchConditions): array
    {
        // 事業所絞り込み
        $targetOfficeFacilityIds = SettingsHelper::getReportBankTransferFeeTargetOfficeFacilities();

        return DB::table('m_departments')
            ->leftJoin('m_office_facilities', 'm_departments.id', '=', 'm_office_facilities.department_id')
            ->leftJoin('deposit_orders', function ($join) use ($searchConditions) {
                $join->on('m_office_facilities.id', '=', 'deposit_orders.office_facilities_id')
                    ->where('deposit_orders.transaction_type_id', TransactionType::ON_ACCOUNT);
                if (!empty($searchConditions['start_date'])) {
                    $join->where('deposit_orders.order_date', '>=', $searchConditions['start_date']);
                }
                if (!empty($searchConditions['end_date'])) {
                    $join->where('deposit_orders.order_date', '<=', $searchConditions['end_date']);
                }
            })
            ->leftJoin('deposit_order_details', 'deposit_orders.id', '=', 'deposit_order_details.deposit_order_id')
            ->whereIn('m_office_facilities.id', $targetOfficeFacilityIds)
            ->whereNull('deposit_orders.deleted_at')
            ->select(
                'm_departments.id as department_id',
                'm_departments.name as department_name',
                'm_office_facilities.id as office_facility_id',
                'm_office_facilities.code as office_facility_code',
                'm_office_facilities.name as office_facility_name',
                DB::raw('DATE_FORMAT(deposit_orders.order_date, "%Y-%m") as order_month'),
                DB::raw('COUNT(DISTINCT deposit_orders.id) as order_count'),
                DB::raw('SUM(deposit_orders.deposit) as total_deposit'),
                DB::raw('SUM(deposit_order_details.amount_fee) as total_amount_fee')
            )
            ->groupBy(
                'm_departments.id',
                'm_departments.name',
                'm_office_facilities.id',
                'm_office_facilities.code',
                'm_office_facilities.name',
                DB::raw('DATE_FORMAT(deposit_orders.order_date, "%Y-%m")')
            )
            ->get()
            ->toArray();
    }
}
