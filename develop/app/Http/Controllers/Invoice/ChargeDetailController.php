<?php

/**
 * 請求明細一覧画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Invoice;

use App\Enums\DepositMethodType;
use App\Helpers\ClosingDateHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Invoice\ChargeDetailSearchRequest;
use App\Models\Invoice\ChargeData;
use App\Models\Invoice\ChargeDetail;
use App\Models\Master\MasterCustomer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * 請求明細一覧画面用コントローラー
 */
class ChargeDetailController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * ChargeDetailController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param ChargeDetailSearchRequest $request
     * @return View
     */
    public function index(ChargeDetailSearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLCommonKey(), URL::full());

        // 得意先のデフォルトセット
        if (!isset($search_condition_input_data['customer_id'])) {
            $search_condition_input_data['customer_id'] = null;
        }
        // 請求期間のデフォルトセット
        if (!isset($search_condition_input_data['charge_date'])) {
            $search_condition_input_data['charge_date'] = Carbon::now()->format('Y-m');
        }
        // 締日区分のデフォルトセット
        if (!isset($search_condition_input_data['closing_date'])) {
            $search_condition_input_data['closing_date'] = 0;
        }

        // 締年月日の範囲年月取得
        [$charge_date_start, $charge_date_end] = ClosingDateHelper::getChargeCloseTermDate(
            $search_condition_input_data['charge_date'], $search_condition_input_data['closing_date']);

        // 請求データ
        $charge_data = ChargeData::getChargeData(
            $search_condition_input_data['customer_id'],
            $search_condition_input_data['charge_date'],
            $search_condition_input_data['closing_date']
        );

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 得意先データ */
                'customers' => MasterCustomer::getBillingCustomer(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                /** 締年月日データ */
                'charge_closing_date_display' => ClosingDateHelper::getChargeClosingDateDisplay($charge_date_end, $search_condition_input_data['closing_date']),
                'charge_date_start' => $charge_date_start,
                'charge_date_end' => $charge_date_end,
                /** 請求データ */
                'charge_data' => $charge_data[0],
                /** 伝票データ */
                'order_details' => ChargeDetail::getOrder(
                    $charge_data[0]->charge_data_sales_ids ?? [],
                    $charge_data[0]->charge_data_deposit_ids ?? []),
            ],
            'deposit_method_types' => DepositMethodType::asSelectArray(),
        ];

        return view('invoice.charge_detail.index', $data);
    }
}
