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
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AccountsReceivableBalanceListExcelService extends AbstractExcelService
{
    /** 明細行の始まりの行 */
    protected int $start_row_detail = 8;

    /** 明細行の最大行 */
    protected int $max_row_detail = 18;

    public function __construct()
    {
        $prefix = '_' . Carbon::now()->format('YmdHis');
        $downloadExcelFileName = config('consts.excel.filename.accounts_receivable_balance_list')
            . $prefix . config('consts.excel.filename.file_extension');
        $downloadPdfFileName = config('consts.pdf.filename.accounts_receivable_balance_list')
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
                (isset($data['total_deposit_3_months_ago']) && $data['total_deposit_3_months_ago'] != '0') ||
                (isset($data['total_deposit_2_months_ago']) && $data['total_deposit_2_months_ago'] != '0') ||
                (isset($data['total_deposit_1_months_ago']) && $data['total_deposit_1_months_ago'] != '0') ||
                (isset($data['total_deposit_current_months_ago']) && $data['total_deposit_current_months_ago'] != '0')) {
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

        // テンプレートファイルの読み込み
        $activeSheet = $this->initSpreadSheet(storage_path(
            config('consts.excel.template_path')
            . config('consts.excel.template_file.accounts_receivable_balance_list')
        ), PageSetup::ORIENTATION_LANDSCAPE);

        // タイトル
        $branch_name = MasterOfficeFacility::query()->where('id', $searchConditions['office_facility_id'])->value('name');
        $base_month = Carbon::parse($searchConditions['year_month'] . '-01');
        $start_month = $base_month->copy()->subMonths(3)->format('Y年m月');
        $end_month = $base_month->format('Y年m月');
        $header_text = "{$branch_name}                      ＊＊ 売掛残高一覧 ＊＊    {$start_month}～ {$end_month}";
        $activeSheet->setCellValue('B2', $header_text);
        $activeSheet->mergeCells('B2:Q2');
        $activeSheet->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Carbonでパース（"Y年m月"の形式に合わせてフォーマット指定）
        $start_date = Carbon::createFromFormat('Y年m月', $start_month);
        $end_date = Carbon::createFromFormat('Y年m月', $end_month);

        // 1年前に戻して表示
        $start_date->subYear();
        $end_date->subYear();
        $start_month_1y_ago = $start_date->format('Y年m月');
        $end_month_1y_ago = $end_date->format('Y年m月');
        $output = "{$start_month_1y_ago} ～ {$end_month_1y_ago}";

        // ヘッダー関連の書式設定
        $activeSheet->getStyle('A2:T4')->getFont()->setName('ＭＳ Ｐゴシック')->setSize(11)->setBold(true);

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

        // 罫線・配置
        $activeSheet->getStyle('B2:S4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $activeSheet->getStyle('T4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $activeSheet->getStyle('B4:T4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $activeSheet->getStyle('R2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // セル結合
        $activeSheet->mergeCells('F3:H3');
        $activeSheet->mergeCells('I3:K3');
        $activeSheet->mergeCells('L3:N3');
        $activeSheet->mergeCells('O3:Q3');
        $activeSheet->mergeCells('R2:S3');
        $activeSheet->getStyle('F3:Q3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 列名
        $activeSheet->setCellValue('R2', '期間内売上比較');
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
        $activeSheet->setCellValue('R4', "$output");
        $activeSheet->setCellValue('S4', "{$start_month}～ {$end_month}");
        $activeSheet->setCellValue('T4', '昨年との差');

        // 列幅調整
        $activeSheet->getColumnDimension('B')->setAutoSize(true);
        $activeSheet->getColumnDimension('D')->setAutoSize(true);
        $activeSheet->getColumnDimension('E')->setAutoSize(true);
        $activeSheet->getColumnDimension('R')->setAutoSize(true);
        $activeSheet->getColumnDimension('S')->setAutoSize(true);
        $activeSheet->getColumnDimension('T')->setAutoSize(true);

        // フォントサイズの調整
        $activeSheet->getStyle('B2')->getFont()->setSize(14)->setBold(true);
        $activeSheet->getStyle('R4')->getFont()->setSize(9)->setBold(true);
        $activeSheet->getStyle('S4')->getFont()->setSize(9)->setBold(true);
        $activeSheet->getStyle('R2')->getFont()->setSize(14)->setBold(true);
        $activeSheet->getStyle('T4')->getFont()->setSize(10)->setBold(true);

        foreach (range('E', 'Q') as $col) {
            $activeSheet->getColumnDimension($col)->setAutoSize(false)->setWidth(10.75);
        }

        // 列の表示
        $activeSheet->getColumnDimension('C')->setVisible(false);
        $activeSheet->getColumnDimension('D')->setVisible(false);

        // ウインドウ枠の固定
        $activeSheet->freezePane('E5');

        // データ出力開始行
        $row = 5;

        // summary_group_id（部門）でグループ
        $grouped = collect($outputData)->groupBy('customer_summary_group_id');

        foreach ($grouped as $group_row) {
            $department_row = $row;
            $has_data = false; // 部門内で出力確認

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

                // 顧客行の出力
                $activeSheet->setCellValue("B{$row}", $data['customer_name']);
                $activeSheet->setCellValue("C{$row}", $data['employee_code']);
                $activeSheet->setCellValue("D{$row}", $data['employee_name']);
                if ($searchConditions['department_id'] === '2') {
                    $activeSheet->setCellValue("C{$row}", $employee_code);
                    $activeSheet->setCellValue("D{$row}", $employee_name);
                }

                // 売上
                $activeSheet->setCellValue("E{$row}", $data['total_charge_total']);
                $activeSheet->setCellValue("F{$row}", $data['total_sales_3_months_ago'] ?? 0);
                $activeSheet->setCellValue("I{$row}", $data['total_sales_2_months_ago'] ?? 0);
                $activeSheet->setCellValue("L{$row}", $data['total_sales_1_months_ago'] ?? 0);
                $activeSheet->setCellValue("O{$row}", $data['total_sales_current_months_ago'] ?? 0);

                // 入金
                $activeSheet->setCellValue("G{$row}", $data['total_deposit_3_months_ago'] ?? 0);
                $activeSheet->setCellValue("J{$row}", $data['total_deposit_2_months_ago'] ?? 0);
                $activeSheet->setCellValue("M{$row}", $data['total_deposit_1_months_ago'] ?? 0);
                $activeSheet->setCellValue("P{$row}", $data['total_deposit_current_months_ago'] ?? 0);

                // 残高（数式）
                $activeSheet->setCellValue("H{$row}", "=E{$row}+F{$row}-G{$row}");
                $activeSheet->setCellValue("K{$row}", "=H{$row}+I{$row}-J{$row}");
                $activeSheet->setCellValue("N{$row}", "=K{$row}+L{$row}-M{$row}");
                $activeSheet->setCellValue("Q{$row}", "=N{$row}+O{$row}-P{$row}");

                // 期間内売上比較
                $activeSheet->setCellValue("R{$row}", $data['total_prev_charge_total'] ?? 0);  // 前年分
                $activeSheet->setCellValue("S{$row}", "=F{$row}+I{$row}+L{$row}+O{$row}");             // 今年分
                $activeSheet->setCellValue("T{$row}", "=S{$row}-R{$row}");                     // 昨年との差

                // 罫線
                $border_range = "B{$row}:T{$row}";
                $activeSheet->getStyle($border_range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // フォント・文字装飾
                $style_range = "{$row}";
                $activeSheet->getStyle($style_range)->getFont()->setName('ＭＳ Ｐゴシック');
                $activeSheet->getStyle($style_range)->getFont()->setSize(11); // 適宜調整
                $activeSheet->getStyle($style_range)->getFont()->setBold(true);
                $activeSheet->getDefaultRowDimension()->setRowHeight(18.75);

                $has_data = true; // 出力があったらtrue

                ++$row;
            }

            // 出力対象がなければ部門計を出力しない
            if (!$has_data) {
                continue;
            }

            $department_cell = $row + 1;

            // ☆部門計☆ 出力
            $activeSheet->setCellValue("B{$department_cell}", '☆部門計☆');

            // 合計対象列 E〜Q を対象にSUM
            foreach (range('E', 'T') as $col) {
                $activeSheet->setCellValue("{$col}{$department_cell}",
                    "=SUM({$col}{$department_row}:{$col}" . ($row - 1) . ')');
            }

            // ☆部門計☆の行
            $department_total_row = $department_cell;
            $department_all_total_row[] = $department_total_row;

            // 罫線とフォント
            $style_range = 'B' . ($row) . ':T' . ($department_cell);
            $activeSheet->getStyle($style_range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $activeSheet->getStyle($style_range)->getFont()->setBold(true);

            // 空白行の挿入
            $blank_row = $department_cell + 1;

            // 部門計の直後の空白行にも罫線を引く（次の部門が存在する場合）
            if ($grouped->last() !== $group_row) {
                // 空白行に罫線だけ引く（内容は空白のまま）
                $activeSheet->getStyle("B{$blank_row}:T{$blank_row}")
                    ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
            $row = $department_cell + 2;
        }

        if (!empty($department_all_total_row)) {
            $total_row = $row;

            $activeSheet->setCellValue("B{$total_row}", '☆合計☆');

            // 部門計を合算する数式
            foreach (['E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'] as $col) {
                $sum_formula_parts = [];
                foreach ($department_all_total_row as $department_total_row) {
                    $sum_formula_parts[] = "{$col}{$department_total_row}";
                }
                $formula = '=IFERROR(SUM(' . implode(',', $sum_formula_parts) . '), 0)';
                $activeSheet->setCellValue("{$col}{$total_row}", $formula);
            }

            // ☆合計☆行の罫線・文字装飾
            $activeSheet->getStyle("B{$total_row}:T{$total_row}")
                ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $activeSheet->getStyle("B{$total_row}:T{$total_row}")->getFont()->setBold(true);
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

        $base_month = Carbon::parse($searchConditions['year_month'] . '-01');
        $three_months_ago_start = $base_month->copy()->subMonths(3)->startOfMonth();
        $three_months_ago_end = $base_month->copy()->subMonths(3)->endOfMonth();
        $two_months_ago_start = $base_month->copy()->subMonths(2)->startOfMonth();
        $two_months_ago_end = $base_month->copy()->subMonths(2)->endOfMonth();
        $one_month_ago_start = $base_month->copy()->subMonth()->startOfMonth();
        $one_month_ago_end = $base_month->copy()->subMonth()->endOfMonth();
        $current_month_start = $base_month->copy()->startOfMonth();
        $current_month_end = $base_month->copy()->endOfMonth();

        $closing_month = $base_month->copy()->subMonths(4)->format('Ym');
        $prev_closing_start_month = $three_months_ago_start->copy()->subYears(1)->format('Ym');
        $prev_closing_end_month = $current_month_start->copy()->subYears(1)->format('Ym');

        // 2-4)売上伝票テーブルの抽出（画面の年月度～3か月前を対象期間とし、4か月分をそれぞれ抽出集計する）
        $salesSub = DB::table('sales_orders')
            ->select(
                'customer_id',
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$three_months_ago_start}' AND '{$three_months_ago_end}' THEN sales_total ELSE 0 END) AS total_sales_3_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$two_months_ago_start}' AND '{$two_months_ago_end}' THEN sales_total ELSE 0 END) AS total_sales_2_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$one_month_ago_start}' AND '{$one_month_ago_end}' THEN sales_total ELSE 0 END) AS total_sales_1_months_ago"),
                DB::raw("SUM(CASE WHEN order_date BETWEEN '{$current_month_start}' AND '{$current_month_end}' THEN sales_total ELSE 0 END) AS total_sales_current_months_ago")
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
                'm_customers.name AS customer_name',
                'm_customers.summary_group_id AS customer_summary_group_id',
                DB::raw('CASE WHEN d.id = 2 THEN manager.code ELSE emp.code END AS employee_code'),
                DB::raw('CASE WHEN d.id = 2 THEN manager.name ELSE emp.name END AS employee_name'),
                DB::raw('cd.charge_total as total_charge_total'),
                DB::raw('sales_summary.total_sales_3_months_ago'),
                DB::raw('sales_summary.total_sales_2_months_ago'),
                DB::raw('sales_summary.total_sales_1_months_ago'),
                DB::raw('sales_summary.total_sales_current_months_ago'),
                DB::raw('deposit_summary.total_deposit_3_months_ago'),
                DB::raw('deposit_summary.total_deposit_2_months_ago'),
                DB::raw('deposit_summary.total_deposit_1_months_ago'),
                DB::raw('deposit_summary.total_deposit_current_months_ago'),
                DB::raw('SUM(cd_prev.charge_total) as total_prev_charge_total')
            )
             // 2-2)担当者の特定
            ->leftJoin('m_office_facilities AS of', 'm_customers.office_facilities_id', '=', 'of.id')
            ->leftJoin('m_employees AS manager', 'of.manager_id', '=', 'manager.id')
            ->leftJoin('m_employees AS emp', 'm_customers.employee_id', '=', 'emp.id')
            ->leftJoin('m_departments AS d', 'm_customers.department_id', '=', 'd.id')
             // 2-3)請求データテーブルの抽出
            ->leftJoin('charge_data AS cd', function ($join) use ($closing_month, $department_id, $office_facility_id) {
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
            // 2-6)請求データの抽出 (画面の年月度から前年の4か月分を抽出集計する)
            ->leftjoin('charge_data AS cd_prev', function ($join) use ($prev_closing_start_month, $prev_closing_end_month, $department_id, $office_facility_id) {
                $join->on('cd_prev.customer_id', '=', 'm_customers.id')
                    ->where('cd_prev.closing_ym', '>=', $prev_closing_start_month)
                    ->where('cd_prev.closing_ym', '<=', $prev_closing_end_month)
                    ->where('cd_prev.department_id', '=', $department_id)
                    ->where('cd_prev.office_facilities_id', '=', $office_facility_id);
            })
            ->groupBy(
                'customer_name',
                'employee_code',
                'employee_name',
                'total_charge_total',
                'customer_summary_group_id',
                'sales_summary.total_sales_3_months_ago',
                'sales_summary.total_sales_2_months_ago',
                'sales_summary.total_sales_1_months_ago',
                'sales_summary.total_sales_current_months_ago',
                'deposit_summary.total_deposit_3_months_ago',
                'deposit_summary.total_deposit_2_months_ago',
                'deposit_summary.total_deposit_1_months_ago',
                'deposit_summary.total_deposit_current_months_ago'
            )
            ->get()
            ->toArray();
    }
}
