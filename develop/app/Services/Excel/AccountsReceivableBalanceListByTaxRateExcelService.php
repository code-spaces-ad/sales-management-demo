<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Enums\TransactionType;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterOfficeFacility;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AccountsReceivableBalanceListByTaxRateExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 5;

    /** 明細行の最大行 */
    protected int $max_row_detail = 18;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.accounts_receivable_balance_list_by_tax_rate')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.accounts_receivable_balance_list_by_tax_rate')
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

        // すべての顧客の売上・入金データが0かどうかを確認
        foreach ($outputData as $data) {
            // いずれかの売上・入金データが0でなければ有効なデータがある
            if (
                (isset($data['total_sales_3_months_ago']) && $data['total_sales_3_months_ago'] != '0') ||
                (isset($data['total_sales_2_months_ago']) && $data['total_sales_2_months_ago'] != '0') ||
                (isset($data['total_sales_1_months_ago']) && $data['total_sales_1_months_ago'] != '0') ||
                (isset($data['total_sales_current_months_ago']) && $data['total_sales_current_months_ago'] != '0') ||
                (isset($data['total_sales_normal_3_months_ago']) && $data['total_sales_normal_3_months_ago'] != '0') ||
                (isset($data['total_sales_normal_2_months_ago']) && $data['total_sales_normal_2_months_ago'] != '0') ||
                (isset($data['total_sales_normal_1_months_ago']) && $data['total_sales_normal_1_months_ago'] != '0') ||
                (isset($data['total_sales_normal_current_months_ago']) && $data['total_sales_normal_current_months_ago'] != '0') ||
                (isset($data['total_sales_reduced_3_months_ago']) && $data['total_sales_reduced_3_months_ago'] != '0') ||
                (isset($data['total_sales_reduced_2_months_ago']) && $data['total_sales_reduced_2_months_ago'] != '0') ||
                (isset($data['total_sales_reduced_1_months_ago']) && $data['total_sales_reduced_1_months_ago'] != '0') ||
                (isset($data['total_sales_reduced_current_months_ago']) && $data['total_sales_reduced_current_months_ago'] != '0') ||
                (isset($data['total_sales_free_3_months_ago']) && $data['total_sales_free_3_months_ago'] != '0') ||
                (isset($data['total_sales_free_2_months_ago']) && $data['total_sales_free_2_months_ago'] != '0') ||
                (isset($data['total_sales_free_1_months_ago']) && $data['total_sales_free_1_months_ago'] != '0') ||
                (isset($data['total_sales_free_current_months_ago']) && $data['total_sales_free_current_months_ago'] != '0') ||
                (isset($data['total_deposit_3_months_ago']) && $data['total_deposit_3_months_ago'] != '0') ||
                (isset($data['total_deposit_2_months_ago']) && $data['total_deposit_2_months_ago'] != '0') ||
                (isset($data['total_deposit_1_months_ago']) && $data['total_deposit_1_months_ago'] != '0') ||
                (isset($data['total_deposit_current_months_ago']) && $data['total_deposit_current_months_ago'] != '0')
            ) {
                return true;
            }
        }

        // すべての顧客データが0の場合は、有効なデータがないと判断
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
        // 営業所の場合、担当者は事業所テーブルから取得する
        $employee_code = '';
        $employee_name = '';
        if ($searchConditions['department_id'] === '2') {
            $manager_id = MasterOfficeFacility::query()->where('id', $searchConditions['office_facility_id'])->value('manager_id');
            $m_employee = MasterEmployee::query()->where('id', $manager_id)->first();
            $employee_code = $m_employee->code;
            $employee_name = $m_employee->name;
        }

        // テンプレートファイル読み込み
        $activeSheet = $this->initSpreadSheet(storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.accounts_receivable_balance_list_by_tax_rate')
        ), PageSetup::ORIENTATION_LANDSCAPE);

        // タイトル
        $branch_name = MasterOfficeFacility::query()->where('id', $searchConditions['office_facility_id'])->value('name');
        $base_month = Carbon::parse($searchConditions['year_month'] . '-01');
        $start_month = $base_month->copy()->subMonths(3)->format('Y年m月');
        $end_month = $base_month->format('Y年m月');
        $header_text = "{$branch_name}                      ＊＊ 売掛残高一覧 ＊＊    {$start_month}～ {$end_month}";
        $activeSheet->setCellValue('A2', $header_text);
        $activeSheet->mergeCells('A2:Q2');
        $activeSheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // ヘッダー関連の書式設定
        $activeSheet->getStyle('A2:Q4')->getFont()->setName('ＭＳ Ｐゴシック')->setSize(11)->setBold(true);

        $month_labels = [
            $base_month->copy()->subMonths(3)->format('n月'),
            $base_month->copy()->subMonths(2)->format('n月'),
            $base_month->copy()->subMonths(1)->format('n月'),
            $base_month->format('n月'),
        ];
        $activeSheet->setCellValue('F3', $month_labels[0]);
        $activeSheet->setCellValue('I3', $month_labels[1]);
        $activeSheet->setCellValue('L3', $month_labels[2]);
        $activeSheet->setCellValue('O3', $month_labels[3]);

        // セル結合
        $activeSheet->mergeCells('F3:H3');
        $activeSheet->mergeCells('I3:K3');
        $activeSheet->mergeCells('L3:N3');
        $activeSheet->mergeCells('O3:Q3');
        $activeSheet->getStyle('F3:Q3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 列名
        $activeSheet->setCellValue('A4', '得意先CD');
        $activeSheet->setCellValue('B4', '得意先名');
        $activeSheet->setCellValue('C4', '担当者CD');
        $activeSheet->setCellValue('D4', '担当者名');
        $activeSheet->setCellValue('E4', '繰越額');
        $activeSheet->setCellValue('F4', '売 上');
        $activeSheet->setCellValue('G4', '入 金');
        $activeSheet->setCellValue('H4', '残 高');
        $activeSheet->setCellValue('I4', '売 上');
        $activeSheet->setCellValue('J4', '入 金');
        $activeSheet->setCellValue('K4', '残 高');
        $activeSheet->setCellValue('L4', '売 上');
        $activeSheet->setCellValue('M4', '入 金');
        $activeSheet->setCellValue('N4', '残 高');
        $activeSheet->setCellValue('O4', '売 上');
        $activeSheet->setCellValue('P4', '入 金');
        $activeSheet->setCellValue('Q4', '残 高');

        // 罫線・配置
        $activeSheet->getStyle('A2:Q4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $activeSheet->getStyle('D4:Q4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // 列幅調整
        $activeSheet->getColumnDimension('B')->setAutoSize(true);
        $activeSheet->getColumnDimension('D')->setAutoSize(true);
        $activeSheet->getColumnDimension('E')->setAutoSize(true);

        foreach (range('E', 'Q') as $col) {
            $activeSheet->getColumnDimension($col)->setAutoSize(false)->setWidth(10.75);
        }

        $activeSheet->getColumnDimension('A')->setVisible(false);
        $activeSheet->getColumnDimension('C')->setVisible(false);

        // ウインドウ枠の固定
        $activeSheet->freezePane('A5');

        // データ出力開始行
        $row = 5;

        // summary_group_id（部門）でグループ化
        $grouped = collect($outputData)->groupBy('customer_summary_group_id');

        foreach ($grouped as $group_row) {
            $department_row = $row;
            // ★ 部門ごとに顧客の合計行の行番号初期化
            $customer_summary_rows = [];

            foreach ($group_row as $data) {
                // 値が全てゼロの場合は出力しない
                if (empty($data['total_charge_total'])
                    && empty($data['total_sales_3_months_ago'])
                    && empty($data['total_sales_2_months_ago'])
                    && empty($data['total_sales_1_months_ago'])
                    && empty($data['total_sales_current_months_ago'])
                    && empty($data['total_deposit_3_months_ago'])
                    && empty($data['total_deposit_2_months_ago'])
                    && empty($data['total_deposit_1_months_ago'])
                    && empty($data['total_deposit_current_months_ago'])
                ) {
                    continue;
                }
                // 顧客の合計行の行番号を記録
                $customer_summary_rows[] = $row;

                $activeSheet->setCellValue("A{$row}", $data['customer_code']);
                $activeSheet->setCellValue("B{$row}", $data['customer_name']);
                $activeSheet->setCellValue("C{$row}", $data['employee_code']);
                $activeSheet->setCellValue("D{$row}", $data['employee_name']);
                if ($searchConditions['department_id'] === '2') {
                    $activeSheet->setCellValue("C{$row}", $employee_code);
                    $activeSheet->setCellValue("D{$row}", $employee_name);
                }

                $activeSheet->setCellValue("E{$row}", $data['total_charge_total']);
                $activeSheet->setCellValue("F{$row}", $data['total_sales_3_months_ago'] ?? 0);
                $activeSheet->setCellValue("I{$row}", $data['total_sales_2_months_ago'] ?? 0);
                $activeSheet->setCellValue("L{$row}", $data['total_sales_1_months_ago'] ?? 0);
                $activeSheet->setCellValue("O{$row}", $data['total_sales_current_months_ago'] ?? 0);

                $activeSheet->setCellValue('D' . ($row + 1), '売上 8%');
                $activeSheet->setCellValue('F' . ($row + 1), $data['total_sales_reduced_3_months_ago'] ?? 0);
                $activeSheet->setCellValue('I' . ($row + 1), $data['total_sales_reduced_2_months_ago'] ?? 0);
                $activeSheet->setCellValue('L' . ($row + 1), $data['total_sales_reduced_1_months_ago'] ?? 0);
                $activeSheet->setCellValue('O' . ($row + 1), $data['total_sales_reduced_current_months_ago'] ?? 0);

                $activeSheet->setCellValue('D' . ($row + 2), '売上 10%');
                $activeSheet->setCellValue('F' . ($row + 2), $data['total_sales_normal_3_months_ago'] ?? 0);
                $activeSheet->setCellValue('I' . ($row + 2), $data['total_sales_normal_2_months_ago'] ?? 0);
                $activeSheet->setCellValue('L' . ($row + 2), $data['total_sales_normal_1_months_ago'] ?? 0);
                $activeSheet->setCellValue('O' . ($row + 2), $data['total_sales_normal_current_months_ago'] ?? 0);

                $activeSheet->setCellValue('D' . ($row + 3), '売上 非課税');
                $activeSheet->setCellValue('F' . ($row + 3), ($data['total_sales_3_months_ago'] ?? 0)
                    - ($data['total_sales_reduced_3_months_ago'] ?? 0) - ($data['total_sales_normal_3_months_ago'] ?? 0));
                $activeSheet->setCellValue('I' . ($row + 3), ($data['total_sales_2_months_ago'] ?? 0)
                    - ($data['total_sales_reduced_2_months_ago'] ?? 0) - ($data['total_sales_normal_2_months_ago'] ?? 0));
                $activeSheet->setCellValue('L' . ($row + 3), ($data['total_sales_1_months_ago'] ?? 0)
                    - ($data['total_sales_reduced_1_months_ago'] ?? 0) - ($data['total_sales_normal_1_months_ago'] ?? 0));
                $activeSheet->setCellValue('O' . ($row + 3), ($data['total_sales_current_months_ago'] ?? 0)
                    - ($data['total_sales_reduced_current_months_ago'] ?? 0) - ($data['total_sales_normal_current_months_ago'] ?? 0));

                $activeSheet->setCellValue("G{$row}", $data['total_deposit_3_months_ago'] ?? 0);
                $activeSheet->setCellValue("J{$row}", $data['total_deposit_2_months_ago'] ?? 0);
                $activeSheet->setCellValue("M{$row}", $data['total_deposit_1_months_ago'] ?? 0);
                $activeSheet->setCellValue("P{$row}", $data['total_deposit_current_months_ago'] ?? 0);

                // 残高（数式）
                $activeSheet->setCellValue("H{$row}", "=E{$row}+F{$row}-G{$row}");
                $activeSheet->setCellValue("K{$row}", "=H{$row}+I{$row}-J{$row}");
                $activeSheet->setCellValue("N{$row}", "=K{$row}+L{$row}-M{$row}");
                $activeSheet->setCellValue("Q{$row}", "=N{$row}+O{$row}-P{$row}");

                // 罫線
                $borderRange = "A{$row}:Q" . ($row + 3);
                $activeSheet->getStyle($borderRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // フォント・文字装飾
                $styleRange = "{$row}:" . ($row + 3);
                $activeSheet->getStyle($styleRange)->getFont()->setName('ＭＳ Ｐゴシック');
                $activeSheet->getStyle($styleRange)->getFont()->setSize(11); // 適宜調整
                $activeSheet->getStyle($styleRange)->getFont()->setBold(true);
                $activeSheet->getDefaultRowDimension()->setRowHeight(18.75);

                $row += 4;
            }

            // 部門にデータが存在する場合のみ部門計行を出力
            if (!empty($customer_summary_rows)) {
                // 部門計☆行の挿入
                ++$row;

                $dpt_summary_row = $row;

                // B列に「☆部門計☆」を出力
                $activeSheet->setCellValue("B{$dpt_summary_row}", '☆部門計☆');

                // 顧客の売上行を集計
                foreach (['E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'] as $col) {
                    $sumFormulaParts = [];
                    foreach ($customer_summary_rows as $r) {
                        $sumFormulaParts[] = "{$col}{$r}";
                    }

                    // 集計対象がある場合のみSUM式を設定、なければ0を設定
                    if (!empty($sumFormulaParts)) {
                        $formula = '=SUM(' . implode(',', $sumFormulaParts) . ')';
                        $activeSheet->setCellValue("{$col}{$row}", $formula);
                    } else {
                        $activeSheet->setCellValue("{$col}{$row}", 0);
                    }
                }

                // 罫線とフォント
                $styleRange = 'A' . ($row - 1) . ':Q' . $row;
                $activeSheet->getStyle($styleRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $activeSheet->getStyle($styleRange)->getFont()->setBold(true);

                // 部門計を集計
                $department_total_rows[] = $row;

                ++$row;
            }
        }

        // 部門計行がある場合のみ合計行を出力
        if (!empty($department_total_rows)) {
            // 1行空けて合計行を出力
            ++$row;

            $activeSheet->setCellValue("B{$row}", '☆合計☆');
            $total_columns = ['E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'];

            // 合計式を出力
            foreach ($total_columns as $cell_col) {
                $sum_sells = [];

                // 部門計行だけを集める
                foreach ($department_total_rows as $dept_total_row) {
                    if ($dept_total_row !== $row) {
                        $sum_sells[] = $cell_col . $dept_total_row;
                    }
                }

                // 合計式にするか、0をセットするか
                if (count($sum_sells) > 0) {
                    $formula = '=SUM(' . implode(',', $sum_sells) . ')';
                    $activeSheet->setCellValue($cell_col . $row, $formula);
                } else {
                    $activeSheet->setCellValue($cell_col . $row, 0);
                }
            }

            $activeSheet->getStyle("A{$row}:Q{$row}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $activeSheet->getStyle("A{$row}:Q{$row}")->getFont()->setBold(true);
        }

        // 条件付き書式を定義
        $conditional = new Conditional();
        $conditional->setConditionType(Conditional::CONDITION_CELLIS);
        $conditional->setOperatorType(Conditional::OPERATOR_LESSTHAN);
        $conditional->addCondition('0');
        $conditional->getStyle()->getFont()->getColor()->setARGB(Color::COLOR_RED);

        // 各行に設定
        $total_columns = ['E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'];
        foreach ($total_columns as $total_col) {
            // 範囲を指定
            $cell_range = "{$total_col}5:{$total_col}{$row}";

            // 既存の条件付き書式を取得して追加
            $conditional_styles = $activeSheet->getStyle($cell_range)->getConditionalStyles();
            $conditional_styles[] = $conditional;
            $activeSheet->getStyle($cell_range)->setConditionalStyles($conditional_styles);
        }

        return $activeSheet->getParent();
    }

    /**
     * 帳票出力データを取得
     *
     * @param array $searchConditions
     * @return array
     */
    public function getOutputData(array $searchConditions): array
    {
        $department_id = $searchConditions['department_id'] ?? null;
        $office_facility_id = $searchConditions['office_facility_id'] ?? null;

        $baseMonth = Carbon::parse($searchConditions['year_month'] . '-01');
        $three_months_ago_start = $baseMonth->copy()->subMonths(3)->startOfMonth();
        $three_months_ago_end = $baseMonth->copy()->subMonths(3)->endOfMonth();
        $two_months_ago_start = $baseMonth->copy()->subMonths(2)->startOfMonth();
        $two_months_ago_end = $baseMonth->copy()->subMonths(2)->endOfMonth();
        $one_month_ago_start = $baseMonth->copy()->subMonth()->startOfMonth();
        $one_month_ago_end = $baseMonth->copy()->subMonth()->endOfMonth();
        $current_month_start = $baseMonth->copy()->startOfMonth();
        $current_month_end = $baseMonth->copy()->endOfMonth();

        $closing_month = $baseMonth->copy()->subMonths(4)->format('Ym');

        // 2-4)売上伝票テーブルの抽出（画面の年月度～3か月前を対象期間とし、4か月分をそれぞれ抽出集計する）
        $salesSub = DB::table('sales_orders')
            ->select(
                'customer_id',
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$three_months_ago_start}' AND '{$three_months_ago_end}' THEN sales_total ELSE 0 END) AS total_sales_3_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$two_months_ago_start}' AND '{$two_months_ago_end}' THEN sales_total ELSE 0 END) AS total_sales_2_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$one_month_ago_start}' AND '{$one_month_ago_end}' THEN sales_total ELSE 0 END) AS total_sales_1_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$current_month_start}' AND '{$current_month_end}' THEN sales_total ELSE 0 END) AS total_sales_current_months_ago"),

                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$three_months_ago_start}' AND '{$three_months_ago_end}' THEN sales_total_normal_out + sales_total_normal_in ELSE 0 END) AS total_sales_normal_3_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$two_months_ago_start}' AND '{$two_months_ago_end}' THEN sales_total_normal_out + sales_total_normal_in ELSE 0 END) AS total_sales_normal_2_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$one_month_ago_start}' AND '{$one_month_ago_end}' THEN sales_total_normal_out + sales_total_normal_in ELSE 0 END) AS total_sales_normal_1_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$current_month_start}' AND '{$current_month_end}' THEN sales_total_normal_out + sales_total_normal_in ELSE 0 END) AS total_sales_normal_current_months_ago"),

                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$three_months_ago_start}' AND '{$three_months_ago_end}' THEN sales_total_reduced_out + sales_total_reduced_in ELSE 0 END) AS total_sales_reduced_3_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$two_months_ago_start}' AND '{$two_months_ago_end}' THEN sales_total_reduced_out + sales_total_reduced_in ELSE 0 END) AS total_sales_reduced_2_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$one_month_ago_start}' AND '{$one_month_ago_end}' THEN sales_total_reduced_out + sales_total_reduced_in ELSE 0 END) AS total_sales_reduced_1_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$current_month_start}' AND '{$current_month_end}' THEN sales_total_reduced_out + sales_total_reduced_in ELSE 0 END) AS total_sales_reduced_current_months_ago"),

                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$three_months_ago_start}' AND '{$three_months_ago_end}' THEN sales_total_free ELSE 0 END) AS total_sales_free_3_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$two_months_ago_start}' AND '{$two_months_ago_end}' THEN sales_total_free ELSE 0 END) AS total_sales_free_2_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$one_month_ago_start}' AND '{$one_month_ago_end}' THEN sales_total_free ELSE 0 END) AS total_sales_free_1_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$current_month_start}' AND '{$current_month_end}' THEN sales_total_free ELSE 0 END) AS total_sales_free_current_months_ago"),
            )
            ->where('department_id', $department_id)
            ->where('office_facilities_id', $office_facility_id)
            ->where('transaction_type_id', TransactionType::ON_ACCOUNT)
            ->whereNull('deleted_at')
            ->groupBy('customer_id');

        // 2-5)入金伝票テーブルの抽出（画面の年月度～3か月前を対象期間とし、4か月分をそれぞれ抽出集計する）
        $depositSub = DB::table('deposit_orders')
            ->select(
                'customer_id',
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$three_months_ago_start}' AND '{$three_months_ago_end}' THEN deposit ELSE 0 END) AS total_deposit_3_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$two_months_ago_start}' AND '{$two_months_ago_end}' THEN deposit ELSE 0 END) AS total_deposit_2_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$one_month_ago_start}' AND '{$one_month_ago_end}' THEN deposit ELSE 0 END) AS total_deposit_1_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$current_month_start}' AND '{$current_month_end}' THEN deposit ELSE 0 END) AS total_deposit_current_months_ago")
            )
            ->where('department_id', $department_id)
            ->where('office_facilities_id', $office_facility_id)
            ->whereNull('deleted_at')
            ->groupBy('customer_id');

        return MasterCustomer::query()
            ->select(
                'm_customers.id AS customer_id',
                'm_customers.code AS customer_code',
                'm_customers.name AS customer_name',
                'm_customers.summary_group_id AS customer_summary_group_id',

                DB::raw('CASE WHEN d.id = 2 THEN manager.code ELSE emp.code END AS employee_code'),
                DB::raw('CASE WHEN d.id = 2 THEN manager.name ELSE emp.name END AS employee_name'),
                DB::raw('cd.charge_total as total_charge_total'),

                DB::raw('sales_summary.total_sales_3_months_ago'),
                DB::raw('sales_summary.total_sales_2_months_ago'),
                DB::raw('sales_summary.total_sales_1_months_ago'),
                DB::raw('sales_summary.total_sales_current_months_ago'),

                DB::raw('sales_summary.total_sales_normal_3_months_ago'),
                DB::raw('sales_summary.total_sales_normal_2_months_ago'),
                DB::raw('sales_summary.total_sales_normal_1_months_ago'),
                DB::raw('sales_summary.total_sales_normal_current_months_ago'),

                DB::raw('sales_summary.total_sales_reduced_3_months_ago'),
                DB::raw('sales_summary.total_sales_reduced_2_months_ago'),
                DB::raw('sales_summary.total_sales_reduced_1_months_ago'),
                DB::raw('sales_summary.total_sales_reduced_current_months_ago'),

                DB::raw('sales_summary.total_sales_free_3_months_ago'),
                DB::raw('sales_summary.total_sales_free_2_months_ago'),
                DB::raw('sales_summary.total_sales_free_1_months_ago'),
                DB::raw('sales_summary.total_sales_free_current_months_ago'),

                DB::raw('deposit_summary.total_deposit_3_months_ago'),
                DB::raw('deposit_summary.total_deposit_2_months_ago'),
                DB::raw('deposit_summary.total_deposit_1_months_ago'),
                DB::raw('deposit_summary.total_deposit_current_months_ago')
            )
            // 2-2)担当者の特定
            ->leftJoin('m_office_facilities AS of', 'm_customers.office_facilities_id', '=', 'of.id')
            ->leftJoin('m_employees AS manager', 'of.manager_id', '=', 'manager.id')
            ->leftJoin('m_employees AS emp', 'm_customers.employee_id', '=', 'emp.id')
            ->leftJoin('m_departments AS d', 'm_customers.department_id', '=', 'd.id')

            // 2-3)請求データテーブルの抽出
            ->leftjoin('charge_data AS cd', function ($join) use ($closing_month, $department_id, $office_facility_id) {
                $join->on('cd.customer_id', '=', 'm_customers.id')
                    ->where('cd.closing_ym', '=', $closing_month)
                    ->where('cd.department_id', '=', $department_id)
                    ->where('cd.office_facilities_id', '=', $office_facility_id);
            })
            // 2-4)売上伝票テーブルの抽出（画面の年月度～3か月前を対象期間とし、4か月分をそれぞれ抽出集計する）
            ->leftJoinSub($salesSub, 'sales_summary', function ($join) {
                $join->on('sales_summary.customer_id', '=', 'm_customers.id');
            })
            // 2-5)入金伝票テーブルの抽出（画面の年月度～3か月前を対象期間とし、4か月分をそれぞれ抽出集計する）
            ->leftJoinSub($depositSub, 'deposit_summary', function ($join) {
                $join->on('deposit_summary.customer_id', '=', 'm_customers.id');
            })
            ->groupBy(
                'customer_id',
                'customer_code',
                'customer_name',
                'employee_code',
                'employee_name',
                'total_charge_total',
                'sales_summary.total_sales_3_months_ago',
                'sales_summary.total_sales_2_months_ago',
                'sales_summary.total_sales_1_months_ago',
                'sales_summary.total_sales_current_months_ago',
                'sales_summary.total_sales_normal_3_months_ago',
                'sales_summary.total_sales_normal_2_months_ago',
                'sales_summary.total_sales_normal_1_months_ago',
                'sales_summary.total_sales_normal_current_months_ago',
                'sales_summary.total_sales_reduced_3_months_ago',
                'sales_summary.total_sales_reduced_2_months_ago',
                'sales_summary.total_sales_reduced_1_months_ago',
                'sales_summary.total_sales_reduced_current_months_ago',
                'sales_summary.total_sales_free_3_months_ago',
                'sales_summary.total_sales_free_2_months_ago',
                'sales_summary.total_sales_free_1_months_ago',
                'sales_summary.total_sales_free_current_months_ago',
                'deposit_summary.total_deposit_3_months_ago',
                'deposit_summary.total_deposit_2_months_ago',
                'deposit_summary.total_deposit_1_months_ago',
                'deposit_summary.total_deposit_current_months_ago'
            )
            ->orderBy('m_customers.summary_group_id', 'ASC')
            ->orderBy('m_customers.id', 'ASC')
            ->get()
            ->toArray();
    }
}
