<?php

/**
 * 得意先マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\ChargeClosingHelper;
use App\Helpers\ClosingDateHelper;
use App\Helpers\CodeHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\CustomerEditRequest;
use App\Http\Requests\Master\CustomerSearchRequest;
use App\Models\Invoice\ChargeData;
use App\Models\Master\MasterCustomer;
use App\Services\Master\MasterCustomerService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 得意先マスター画面用コントローラー
 */
class MasterCustomerController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    protected MasterCustomerService $service;

    /**
     * MasterCustomerController constructor.
     *
     * @param MasterCustomerService $service
     */
    public function __construct(MasterCustomerService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Show the application dashboard.
     *
     * @param CustomerSearchRequest $request
     * @return View
     */
    public function index(CustomerSearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLMasterKey(), URL::full());

        return view('master.customers.index', $this->service->index($search_condition_input_data));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterCustomer();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;
        $target_record_data->sort_code = CodeHelper::getNextUsableSortCode('m_customers', 0);

        SessionHelper::forgetSessionForMismatchURL('*master/customers*', $this->refURLMasterKey());

        return view('master.customers.create_edit', $this->service->create($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CustomerEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(CustomerEditRequest $request): RedirectResponse
    {
        [$error_flag, $message] = $this->service->store($request);

        // 一覧画面へリダイレクト
        return redirect(route('master.customers.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterCustomer $customer
     * @return View
     */
    public function edit(MasterCustomer $customer): View
    {
        return view('master.customers.create_edit', $this->service->edit($customer));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CustomerEditRequest $request
     * @param MasterCustomer $customer
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(CustomerEditRequest $request, MasterCustomer $customer): RedirectResponse
    {
        [$error_flag, $message] = $this->service->update($request, $customer);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterCustomer $customer
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterCustomer $customer): RedirectResponse
    {
        [$error_flag, $message] = $this->service->destroy($customer);

        // 一覧画面へリダイレクト
        return redirect(route('master.customers.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Excelダウンロード
     *
     * @param CustomerSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(CustomerSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        [$customers, $filename, $headings, $filters] = $this->service->downloadExcel($search_condition_input_data);

        if ($customers->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.customers.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $customers->exportExcel($filename, $headings, $filters);
    }

    /**
     * 得意先の残高を返す
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBillingBalance(Request $request): JsonResponse
    {
        $order_date = new Carbon($request->input('order_date'));
        $customer_id = $request->input('customer_id');

        // 締データの取得（締められているかどうか）
        $charge_closing = ChargeClosingHelper::getChargeClosing($customer_id, $order_date);
        $close_info = '';
        if ($charge_closing) {
            // 締済みの場合
            $charge_data = ChargeData::where('customer_id', $customer_id)
                ->where('charge_start_date', '<=', $order_date)
                ->where('charge_end_date', '>=', $order_date)
                ->first();
            // 前回請求額+(今回売上額+消費税額)
            $charge_total = $charge_data->before_charge_total +
                ($charge_data->sales_total + $charge_data->sales_tax_total);
        } else {
            // 未締の場合
            $charge_data = ChargeData::where('customer_id', $customer_id)->OrderbyDesc('created_at')->first();
            if (!is_null($charge_data)) {
                $close_info = ClosingDateHelper::getChargeClosingDateBalanceDisplay(
                    $charge_data->charge_end_date, $charge_data->closing_date);
            }
            // 締データがある場合は「今回請求額」、締データが無い場合は得意先マスタ「開始売掛残高」を表示
            $charge_total = $charge_data->charge_total
                ?? MasterCustomer::find($customer_id)->start_account_receivable_balance;
        }

        return response()->json(
            [
                'charge_total' => $charge_total,
                'close_info' => $close_info,
            ]
        );
    }
}
