<?php

/**
 * 請求締処理画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Invoice;

use App\Helpers\ChargeClosingHelper;
use App\Helpers\ClosingDateHelper;
use App\Helpers\LedgerStocksHelper;
use App\Helpers\MessageHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\ChargeClosingCancelRequest;
use App\Http\Requests\Invoice\ChargeClosingSearchRequest;
use App\Http\Requests\Invoice\ChargeClosingStoreRequest;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Sale\SalesOrder;
use App\Services\ChargeClosingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Log;

/**
 * 請求締処理画面用コントローラー
 */
class ChargeClosingController extends Controller
{
    protected ChargeClosingService $service;

    /**
     * サービスをインスタンス
     *
     * @param ChargeClosingService $service
     */
    public function __construct(ChargeClosingService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Show the application dashboard.
     *
     * @param ChargeClosingSearchRequest $request
     * @return View
     */
    public function index(ChargeClosingSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put('search_condition_input_data.charge', $search_condition_input_data);
        Session::put('invoice_url', URL::full());

        // 締年月日の範囲年月取得
        [$charge_date_start, $charge_date_end] = ClosingDateHelper::getChargeCloseTermDate(
            $search_condition_input_data['charge_date'], $search_condition_input_data['closing_date']);

        // 締対象得意先・売上データ取得
        $charge_data = SalesOrder::getTargetClosingCustomerData(
            $search_condition_input_data,
            $charge_date_start,
            $charge_date_end
        );

        // 部門IDと事業所の条件で請求データの絞込
        $charge_data = $this->service->filterChargeDataByDepartmentAndOffice(
            $charge_data,
            $search_condition_input_data['department_id'] ?? null,
            $search_condition_input_data['office_facility_id'] ?? null
        );

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 得意先データ */
                'customers' => MasterCustomer::getClosingBillingCustomer(),
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
                /** 締年月日データ */
                'charge_closing_date_display' => ClosingDateHelper::getChargeClosingDateDisplay(
                    $charge_date_end,
                    $search_condition_input_data['closing_date']
                ),
                'charge_date_start' => $charge_date_start,
                'charge_date_end' => $charge_date_end,
                /** 請求締対象データ */
                'charge_data' => $charge_data,
            ],
        ];

        return view('invoice.charge_closing.index', $data);

    }

    /**
     * 締処理
     *
     * @param ChargeClosingStoreRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(ChargeClosingStoreRequest $request): RedirectResponse
    {
        $customer_ids = explode(',', $request->input('customer_ids'));
        $charge_date = $request->input('charge_date');
        $closing_date = $request->input('closing_date');

        // 帳簿在庫数登録
        LedgerStocksHelper::registLedgerStock($charge_date);

        // 締処理実行
        $result = $this->service->closingProcess($customer_ids, $charge_date, $closing_date, $request->input('department_id'), $request->input('office_facility_id'));
        [$message, $error_flag] = MessageHelper::getChargeClosingStoreMessage(
            json_decode($result->getContent(), true));

        // 一覧画面へリダイレクト
        return redirect(route('invoice.charge_closing.index', Session::get('search_condition_input_data.charge', [])))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 締処理解除
     *
     * @param ChargeClosingCancelRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function cancel(ChargeClosingCancelRequest $request): RedirectResponse
    {
        $charge_data_ids = explode(',', $request->input('charge_data_ids'));
        // 締処理解除実行
        $result = $this->cancelProcess($charge_data_ids);
        [$message, $error_flag] = MessageHelper::getChargeClosingCancelMessage(
            json_decode($result->getContent(), true));

        // 一覧画面へリダイレクト
        return redirect(route('invoice.charge_closing.index', Session::get('search_condition_input_data.charge', [])))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 締処理解除
     *
     * @param array $charge_data_ids
     * @return JsonResponse
     */
    private function cancelProcess(array $charge_data_ids): JsonResponse
    {
        $success_count = 0;
        $failed_count = 0;

        // 締処理解除
        foreach ($charge_data_ids as $charge_data_id) {
            try {
                $result = $this->service->setChargeDataId($charge_data_id)->cancel();

                $content = json_decode($result->content(), true);
                if ($content['message'] === 'success') {
                    ++$success_count;
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
                ++$failed_count;
            }
        }

        $json = [
            'success' => $success_count,
            'failed' => $failed_count,
        ];

        return response()->json($json);
    }

    /**
     * 指定の請求先・年月(未来含む)の請求締処理状況を返す
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function isClosing(Request $request): JsonResponse
    {
        $customer_id = $request->input('customer_id');
        $charge_date = $request->input('charge_date');

        // 請求先IDで検索する
        $billing_customer_id = MasterCustomer::find($customer_id)->billing_customer_id ?? $customer_id;

        return response()->json(
            [
                ChargeClosingHelper::getChargeClosing($billing_customer_id, (new Carbon($charge_date))),
            ]
        );
    }
}
