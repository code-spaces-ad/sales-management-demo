<?php

/**
 * 仕入先マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\ClosingDateHelper;
use App\Helpers\PurchaseClosingHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\SupplierEditRequest;
use App\Http\Requests\Master\SupplierSearchRequest;
use App\Models\Master\MasterSupplier;
use App\Models\PurchaseInvoice\PurchaseClosing;
use App\Services\Master\MasterSupplierService;
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
 * 仕入先マスター画面用コントローラー
 */
class MasterSupplierController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    protected MasterSupplierService $service;

    /**
     * MasterSupplierController constructor
     *
     * @param MasterSupplierService $service.
     */
    public function __construct(MasterSupplierService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Show the application dashboard.
     *
     * @param SupplierSearchRequest $request
     * @return View
     */
    public function index(SupplierSearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLMasterKey(), URL::full());

        return view('master.suppliers.index', $this->service->index($search_condition_input_data));
    }

    /**
     * Excelダウンロード
     *
     * @param SupplierSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(SupplierSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        [$suppliers, $filename, $headings, $filters] = $this->service->downloadExcel($search_condition_input_data);

        if ($suppliers->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.suppliers.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $suppliers->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterSupplier();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;
        $target_record_data->closing_date = 0;

        SessionHelper::forgetSessionForMismatchURL('*master/suppliers*', $this->refURLMasterKey());

        return view('master.suppliers.create_edit', $this->service->create($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SupplierEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(SupplierEditRequest $request): RedirectResponse
    {
        [$error_flag, $message] = $this->service->store($request);

        // 一覧画面へリダイレクト
        return redirect(route('master.suppliers.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterSupplier $supplier
     * @return View
     */
    public function edit(MasterSupplier $supplier): View
    {
        return view('master.suppliers.create_edit', $this->service->edit($supplier));
    }

    /**
     * pdate the specified resource in storage.
     *
     * @param SupplierEditRequest $request
     * @param MasterSupplier $supplier
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(SupplierEditRequest $request, MasterSupplier $supplier): RedirectResponse
    {
        [$error_flag, $message] = $this->service->update($request, $supplier);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterSupplier $supplier
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterSupplier $supplier): RedirectResponse
    {
        [$error_flag, $message] = $this->service->destroy($supplier);

        // 一覧画面へリダイレクト
        return redirect(route('master.suppliers.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 仕入先の残高を返す
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPaymentBalance(Request $request): JsonResponse
    {
        $order_date = new Carbon($request->input('order_date'));
        $supplier_id = $request->input('supplier_id');
        // 締データの取得（締められているかどうか）
        $purchase_closing = PurchaseClosingHelper::getPurchaseClosing($supplier_id, $order_date);
        $close_info = '';
        if ($purchase_closing) {
            // 締済みの場合
            $purchase_closing = PurchaseClosing::query()
                ->where('supplier_id', $supplier_id)
                ->where('purchase_closing_start_date', '<=', $order_date)
                ->where('purchase_closing_end_date', '>=', $order_date)
                ->first();
            // 前回支払額+(今回仕入額+消費税額)
            $purchase_total = $purchase_closing->before_purchase_total +
                ($purchase_closing->purchase_total + $purchase_closing->purchase_tax_total);
        } else {
            // 未締の場合
            $purchase_closing = PurchaseClosing::where('supplier_id', $supplier_id)->OrderbyDesc('created_at')->first();
            if (!is_null($purchase_closing)) {
                $close_info = ClosingDateHelper::getChargeClosingDateBalanceDisplay(
                    $purchase_closing->purchase_closing_end_date, $purchase_closing->closing_date);
            }
            // 締データがある場合は「今回支払額」、締データが無い場合は仕入先マスタ「開始売掛残高」を表示
            $purchase_total = $purchase_closing->purchase_total
                ?? MasterSupplier::find($supplier_id)->start_account_receivable_balance;
        }

        return response()->json(
            [
                'purchase_total' => $purchase_total,
                'close_info' => $close_info,
            ]
        );
    }
}
