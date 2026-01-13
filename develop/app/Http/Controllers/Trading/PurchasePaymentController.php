<?php

/**
 * 支払伝票入力画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Trading;

use App\Enums\TransactionType;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Trading\PurchasePaymentEditRequest;
use App\Http\Requests\Trading\PurchasePaymentSearchRequest;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterSupplier;
use App\Models\Trading\Payment;
use App\Models\Trading\PaymentBill;
use App\Models\Trading\PaymentDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * 支払伝票入力画面用コントローラー
 */
class PurchasePaymentController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * PurchasePaymentController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param PurchasePaymentSearchRequest $request
     * @return View
     */
    public function index(PurchasePaymentSearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();

        Session::put($this->refURLCommonKey(), URL::full());

        // 伝票日付期間のデフォルトセット
        if (!isset($search_condition_input_data['order_date'])) {
            /** 伝票日付（開始）：月初 */
            $search_condition_input_data['order_date']['start'] = Carbon::now()->startOfMonth()->toDateString();
            /** 伝票日付（終了）：月末 */
            $search_condition_input_data['order_date']['end'] = Carbon::now()->endOfMonth()->toDateString();
        }

        $payments_total = Payment::getSearchResultTotal($search_condition_input_data);

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 取引種別データ */
                'transaction_types' => TransactionType::asSelectArray(),
                /** 仕入先データ */
                'suppliers' => MasterSupplier::query()->oldest('name_kana')->get(),
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 支所データ */
                'office_facilities' => MasterOfficeFacility::query()->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'payments' => Payment::getSearchResult($search_condition_input_data),
                'payments_total' => $payments_total[0],
            ],
        ];

        return view('trading.payments.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new Payment();
        $select_order_id = Session::get('select_order_id');
        if ($select_order_id != '') {
            // 選択した伝票をコピー
            $target_record_data = Payment::find($select_order_id);
            Session::put('select_order_id', '');
        }

        SessionHelper::forgetSessionForMismatchURL('*trading/payments*', $this->refURLCommonKey());

        return view('trading.payments.create_edit', $this->sendDataPayment($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PurchasePaymentEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(PurchasePaymentEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        $order_number = Payment::withTrashed()->max('id') + 1;  // 伝票番号

        DB::beginTransaction();

        $payment = new Payment();
        $payment_detail = new PaymentDetail();

        try {
            // 支払伝票
            $payment->order_number = $order_number;
            $payment->transaction_type_id = $request->transaction_type_id;
            $payment->department_id = $request->department_id;
            $payment->office_facilities_id = $request->office_facilities_id;
            $payment->order_date = $request->order_date;
            $payment->note = $request->note;
            $payment->memo = $request->memo;
            $payment->supplier_id = $request->supplier_id;
            $payment->payment = $request->payment ?? 0;
            $payment->creator_id = auth()->user()->id;
            $payment->updated_id = auth()->user()->id;

            $payment->save();

            // 支払伝票詳細
            $payment_detail->payment_id = $payment->id;
            $payment_detail->amount_cash = $request->amount_cash ?? 0;
            $payment_detail->note_cash = $request->note_cash;
            $payment_detail->amount_check = $request->amount_check ?? 0;
            $payment_detail->note_check = $request->note_check;
            $payment_detail->amount_transfer = $request->amount_transfer ?? 0;
            $payment_detail->note_transfer = $request->note_transfer;
            $payment_detail->amount_bill = $request->amount_bill ?? 0;
            $payment_detail->note_bill = $request->note_bill;
            $payment_detail->amount_offset = $request->amount_offset ?? 0;
            $payment_detail->note_offset = $request->note_offset;
            $payment_detail->amount_discount = $request->amount_discount ?? 0;
            $payment_detail->note_discount = $request->note_discount;
            $payment_detail->amount_fee = $request->amount_fee ?? 0;
            $payment_detail->note_fee = $request->note_fee;
            $payment_detail->amount_other = $request->amount_other ?? 0;
            $payment_detail->note_other = $request->note_other;

            $payment_detail->save();

            // 支払伝票_手形リレーション
            if (!is_null($request->bill_date) && !is_null($request->bill_number)) {
                $payment_bill = new PaymentBill();
                $payment_bill->payment_id = $payment->id;
                $payment_bill->bill_date = $request->bill_date;
                $payment_bill->bill_number = $request->bill_number;
                $payment_bill->save();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getPaymentStoreMessage($error_flag, $payment->order_number_zero_fill);

        // 新規登録画面へリダイレクト
        return redirect(route('trading.payments.create'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 伝票コピー（Createページにリダイレクト）
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function copyOrder(Request $request): RedirectResponse
    {
        $input_data = $request->validated();
        // 選択した伝票IDはセッションで渡す
        Session::put('select_order_id', $input_data['select_order_id']);

        // 新規登録画面へリダイレクト
        return redirect(route('trading.payments.create'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Payment $payment
     * @return View
     */
    public function edit(Payment $payment): View
    {
        SessionHelper::forgetSessionForMismatchURL('*trading/*', $this->refURLCommonKey(), '*purchase_invoice/*');

        return view('trading.payments.create_edit', $this->sendDataPayment($payment));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PurchasePaymentEditRequest $request
     * @param Payment $payment
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(PurchasePaymentEditRequest $request, Payment $payment): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {

            // 支払伝票
            $payment->order_date = $request->order_date;
            $payment->transaction_type_id = $request->transaction_type_id;
            $payment->department_id = $request->department_id;
            $payment->office_facilities_id = $request->office_facilities_id;
            $payment->supplier_id = $request->supplier_id;
            $payment->note = $request->note;
            $payment->memo = $request->memo;
            $payment->payment = $request->payment ?? 0;
            $payment->updated_id = auth()->user()->id;

            $payment->save();

            // 支払伝票詳細
            $payment_detail = $payment->PaymentDetail;

            $payment_detail->amount_cash = $request->amount_cash ?? 0;
            $payment_detail->note_cash = $request->note_cash;
            $payment_detail->amount_check = $request->amount_check ?? 0;
            $payment_detail->note_check = $request->note_check;
            $payment_detail->amount_transfer = $request->amount_transfer ?? 0;
            $payment_detail->note_transfer = $request->note_transfer;
            $payment_detail->amount_bill = $request->amount_bill ?? 0;
            $payment_detail->note_bill = $request->note_bill;
            $payment_detail->amount_offset = $request->amount_offset ?? 0;
            $payment_detail->note_offset = $request->note_offset;
            $payment_detail->amount_discount = $request->amount_discount ?? 0;
            $payment_detail->note_discount = $request->note_discount;
            $payment_detail->amount_fee = $request->amount_fee ?? 0;
            $payment_detail->note_fee = $request->note_fee;
            $payment_detail->amount_other = $request->amount_other ?? 0;
            $payment_detail->note_other = $request->note_other;

            $payment_detail->save();

            // 支払伝票_手形リレーション 更新処理
            $this->upsertPurchasePaymentBill($request, $payment);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getPaymentUpdateMessage($error_flag, $payment->order_number_zero_fill);

        // 支払一覧画面へリダイレクト
        return redirect(Session::get('reference_url.common_key'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Payment $payment
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(Payment $payment): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // 支払伝票詳細削除
            $payment->PaymentDetail->delete();   // 論理削除
            if ($payment->PaymentBill != null) {
                // 支払伝票_手形削除
                $payment->PaymentBill->delete();   // 論理削除
            }
            // 支払伝票削除
            $payment->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getPaymentDestoryMessage($error_flag, $payment->order_number_zero_fill);

        // 支払一覧画面へリダイレクト
        return redirect(route('trading.payments.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 支払伝票 手形リレーション 更新処理
     *
     * @param PurchasePaymentEditRequest $request
     * @param Payment $payment
     */
    private function upsertPurchasePaymentBill(PurchasePaymentEditRequest $request, Payment $payment)
    {
        $amount_bill = $request->amount_bill ?? 0;
        if ($amount_bill > 0) {
            // 支払伝票 手形リレーションUPSERT
            $payment->PaymentBill()
                ->updateOrInsert(
                    ['payment_id' => $payment->id],
                    [
                        'bill_date' => $request->bill_date,
                        'bill_number' => $request->bill_number,
                        'deleted_at' => null,
                    ]
                );

            return;
        }
        if ($payment->PaymentBill != null) {
            // 支払伝票_手形リレーション削除
            $payment->PaymentBill->delete();
        }
    }
}
