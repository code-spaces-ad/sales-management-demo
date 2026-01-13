<?php

/**
 * ダッシュボード画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers;

use App\Enums\AggregationType;
use App\Helpers\LogHelper;
use App\Http\Requests\DashboardEditRequest;
use App\Models\Dashboard;
use App\Models\Sale\Ledger\LedgerCategory;
use App\Models\Sale\SalesOrder;
use App\Repositories\Sale\Ledger\CategoryRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ダッシュボード画面用コントローラー
 */
class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        // todo: グラフ作成用のコードが汚すぎる為、要リファクタ
        $categories_array = [];
        $categories = [];
        $order_days = [];
        // 項目名
        $category = ['伝票日付', '肥料', '農薬', '資材', '種子', 'その他', '日計'];
        // 項目毎の色
        $chart_colors = ['#ff9500', '#f62e36', '#b5b5ac', '#009bbf', '#00bb85', '#c1a470', '#00ac9b'];

        // 出力期間（伝票日付）のデフォルトセット
        if (!isset($search_condition_input_data['order_date'])) {
            /** 出力期間（開始）：月初 */
            $search_condition_input_data['order_date']['start'] = Carbon::now()->startOfMonth()->toDateString();
            /** 出力期間（終了）：月末 */
            $search_condition_input_data['order_date']['end'] = Carbon::now()->endOfMonth()->toDateString();
        }

        // 集計種別（金額 or 数量）のデフォルトセット
        if (!isset($search_condition_input_data['aggregation_type'])) {
            /** 金額 */
            $search_condition_input_data['aggregation_type'] = AggregationType::AMOUNT;
        }
        $categories_data = (new CategoryRepository(new LedgerCategory()))
            ->getSearchResult($search_condition_input_data)
            ->toArray();

        // chart.js用のデータを作成
        foreach ($categories_data as $key => $data) {
            if ($key === array_key_first($categories_data)) {
                $categories = array_keys($data);
            }
            foreach ($categories as $val) {
                $categories_array = array_merge_recursive(
                    $categories_array,
                    [
                        $val => [
                            'data' => $data[$val],
                        ],
                    ]
                );
            }
            $order_days[$key] = $data['order_date'];
        }
        foreach ($categories as $key => $val) {
            $categories_array = array_merge_recursive(
                $categories_array,
                [
                    $val => [
                        'label' => $category[$key],
                        'borderColor' => $chart_colors[$key],
                        'lineTension' => 0,
                        'pointBackgroundColor' => $chart_colors[$key],
                        'fill' => false,
                    ],
                ]);
        }

        $key = 0;
        // 連想配列の階層を変更
        foreach ($categories_array as $array) {
            $categories[$key] = $array;
            ++$key;
        }
        // 伝票日付をunset
        unset($categories[0]);
        $categories = array_values($categories);

        $categories = [
            'type' => 'line',
            'data' => [
                'labels' => $order_days,
                'datasets' => $categories,
            ],
            'options' => [
                'responsive' => true,
            ],
        ];

        $data = [
            /** 対象レコード */
            'target_record_data' => Dashboard::query()->find(1),
            /** 未締伝票一覧 */
            'unclosing_data' => $this->getUnClosingCustomerData(),
            /** 種別累計売上票データ */
            'categories' => $categories,
        ];

        return view('dashboard/index', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param DashboardEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(DashboardEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            Dashboard::updateOrCreate(
                ['id' => 1],
                ['news' => $request->news]
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = config('consts.message.common.update_success');
        if ($error_flag) {
            $message = config('consts.message.common.update_failed');
        }

        // 一覧画面へリダイレクト
        return redirect(route('dashboard.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 未締の年月別得意先を取得
     *
     * @return Collection
     */
    public static function getUnClosingCustomerData(): Collection
    {
        $select_col = 'count(*) as order_count, customer_id, m_customers.closing_date,';
        $select_col .= 'CASE ';
        $select_col .= ' WHEN ( m_customers.closing_date = 0 ) THEN date_format(billing_date, "%Y%m") ';
        $select_col .= ' WHEN ( RIGHT(billing_date, 2) > m_customers.closing_date ) THEN date_format(DATE_ADD(billing_date, INTERVAL 1 MONTH), "%Y%m") ';
        $select_col .= 'ELSE date_format(billing_date, "%Y%m") ';
        $select_col .= 'END AS closing_ym';

        $subQuery1 = SalesOrder::query()
            ->selectRaw(
                $select_col
            )
            ->leftJoin('m_customers', 'customer_id', '=', 'm_customers.id')
            ->whereNull('closing_at')
            ->groupByRaw('customer_id, closing_ym, closing_date, m_customers.closing_date')
            ->orderByDesc('closing_ym')
            ->oldest('m_customers.closing_date')
            ->oldest('m_customers.code');

        return $subQuery1->get();
    }
}
