<?php

/**
 * 仕入締明細一覧画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\PurchaseInvoice;

use App\Consts\SessionConst;
use App\Enums\PaymentMethodType;
use App\Helpers\ClosingDateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseInvoice\PurchaseClosingDetailSearchRequest;
use App\Models\Master\MasterSupplier;
use App\Models\PurchaseInvoice\PurchaseClosing;
use App\Models\PurchaseInvoice\PurchaseClosingDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * 仕入締明細一覧画面用コントローラー
 */
class PurchaseClosingDetailController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * PurchaseClosingDetailController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param PurchaseClosingDetailSearchRequest $request
     * @return View
     */
    public function index(PurchaseClosingDetailSearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLCommonKey(), URL::full());

        // 仕入先のデフォルトセット
        if (!isset($search_condition_input_data['supplier_id'])) {
            $search_condition_input_data['supplier_id'] = null;
        }
        // 請求期間のデフォルトセット
        if (!isset($search_condition_input_data['purchase_date'])) {
            $search_condition_input_data['purchase_date'] = Carbon::now()->format('Y-m');
        }
        // 締日区分のデフォルトセット
        if (!isset($search_condition_input_data['closing_date'])) {
            $search_condition_input_data['closing_date'] = 0;
        }

        // 締年月日の範囲年月取得
        [$charge_date_start, $charge_date_end] = ClosingDateHelper::getChargeCloseTermDate(
            $search_condition_input_data['purchase_date'], $search_condition_input_data['closing_date']);

        // 仕入締データ
        $purchase_closing_data = PurchaseClosing::getChargeData(
            $search_condition_input_data['supplier_id'],
            $search_condition_input_data['purchase_date'],
            $search_condition_input_data['closing_date']
        );

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 仕入先データ */
                'suppliers' => MasterSupplier::get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                /** 締年月日データ */
                'charge_closing_date_display' => ClosingDateHelper::getChargeClosingDateDisplay($charge_date_end, $search_condition_input_data['closing_date']),
                'charge_date_start' => $charge_date_start,
                'charge_date_end' => $charge_date_end,
                /** 仕入締データ */
                'purchase_closing_data' => $purchase_closing_data[0],
                /** 伝票データ */
                'order_details' => PurchaseClosingDetail::getOrder(
                    $purchase_closing_data[0]->purchase_closing_purchase_ids ?? [],
                    $purchase_closing_data[0]->purchase_closing_payment_ids ?? []),
            ],
            /** 共通使用セッションキー(URL) */
            'session_common_key' => $this->refURLCommonKey(),
            'payment_method_types' => PaymentMethodType::asSelectArray(),
        ];

        return view('purchase_invoice.purchase_closing_detail.index', $data);
    }
}
