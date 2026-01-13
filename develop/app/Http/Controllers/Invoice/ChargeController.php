<?php

/**
 * 請求一覧画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Invoice;

use App\Enums\DepositMethodType;
use App\Helpers\ClosingDateHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\ChargeSearchRequest;
use App\Models\Invoice\ChargeData;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 請求一覧画面用コントローラー
 */
class ChargeController extends Controller
{
    /**
     * ChargeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param ChargeSearchRequest $request
     * @return View
     */
    public function index(ChargeSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put('invoice_url', URL::full());

        $charge_data_total = ChargeData::getSearchResultTotal($search_condition_input_data);

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 得意先データ */
                'customers' => MasterCustomer::getBillingCustomer(),
                /** 請求締日 */
                'closing_date_list' => ClosingDateHelper::getClosingDateList(),
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 事業所データ */
                'office_facilities' => MasterOfficeFacility::query()->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                /** 請求データ */
                'charge_data' => ChargeData::getSearchResultPaginate($search_condition_input_data),
                'charge_data_total' => $charge_data_total[0],
            ],
        ];

        return view('invoice.charge.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param ChargeSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(ChargeSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.charge_data');

        $headings = [
            '請求先',
            '前回請求額',
            '今回入金額',
            '調整額',
            '繰越残高',
            '今回売上額',
            '消費税',
            '今回総売上額',
            '今回請求額',
            '入金予定日',
        ];

        $charge_data = ChargeData::getSearchResult($search_condition_input_data);

        $filters = [
            function ($charge_data) {
                return $charge_data->customer_name;
            },
            function ($charge_data) {
                return number_format($charge_data->before_charge_total);
            },
            function ($charge_data) {
                return number_format($charge_data->payment_total);
            },
            function ($charge_data) {
                return number_format($charge_data->adjust_amount);
            },
            function ($charge_data) {
                return number_format($charge_data->carryover);
            },
            function ($charge_data) {
                return number_format($charge_data->sales_total);
            },
            function ($charge_data) {
                return number_format($charge_data->sales_tax_total);
            },
            function ($charge_data) {
                return number_format($charge_data->sales_total_amount_total);
            },
            function ($charge_data) {
                return number_format($charge_data->charge_total);
            },
            function ($charge_data) {
                return $charge_data->planned_deposit_at_slash . ' ' . DepositMethodType::getDescription($charge_data->collection_method);
            },
        ];

        if ($charge_data->isEmpty()) {
            // 請求一覧データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('invoice.charge.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $charge_data->exportExcel($filename, $headings, $filters);
    }
}
