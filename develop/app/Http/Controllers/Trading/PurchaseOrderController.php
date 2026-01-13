<?php

/**
 * 仕入一覧、仕入入力画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Trading;

use App\Helpers\ProductHelper;
use App\Helpers\PurchaseOrderHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Trading\PurchaseOrderEditRequest;
use App\Http\Requests\Trading\PurchaseOrderSearchRequest;
use App\Models\Trading\PurchaseOrder;
use App\Services\Trading\PurchaseOrderService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * 仕入一覧、仕入入力画面用コントローラー
 */
class PurchaseOrderController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    protected PurchaseOrderService $service;

    /**
     * サービスをインスタンス
     *
     * @param PurchaseOrderService $service
     */
    public function __construct(PurchaseOrderService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * 仕入伝票一覧画面
     *
     * @param PurchaseOrderSearchRequest $request
     * @return View
     */
    public function index(PurchaseOrderSearchRequest $request): View
    {
        Session::put($this->refURLCommonKey(), URL::full());

        return view('trading.purchase_orders.index',
            $this->service->index(SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults())));
    }

    /**
     * 仕入伝票新規登録画面
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new PurchaseOrder();
        $target_record_data->estimate_date = Carbon::today();   // 見積日付デフォルトセット
        $select_order_id = Session::get('select_purchase_order_id');
        if ($select_order_id != '') {
            // 選択した伝票をコピー
            $target_record_data = PurchaseOrder::query()->find($select_order_id);
            Session::forget('select_purchase_order_id');   // 選択した伝票IDをセッションから削除
        }

        SessionHelper::forgetSessionForMismatchURL('*trading/purchase_orders*', $this->refURLCommonKey());

        return view('trading.purchase_orders.create_edit', $this->service->create($target_record_data));
    }

    /**
     * 仕入伝票新規登録・複製
     *
     * @param PurchaseOrderEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(PurchaseOrderEditRequest $request): RedirectResponse
    {
        [$error_flag, $message] = $this->service->store($request);

        // 複製時は一覧画面へ、新規登録時は新規登録画面へ
        $redirectRoute = null;
        if ($request->input('copy_number', '0') !== '0') {
            $redirectRoute = route('trading.purchase_orders.index');
        }
        if ($request->input('copy_number', '0') === '0') {
            $redirectRoute = route('trading.purchase_orders.create');
        }

        return redirect($redirectRoute)
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 仕入伝票編集画面
     *
     * @param PurchaseOrder $purchase_order
     * @return View
     */
    public function edit(PurchaseOrder $purchase_order): View
    {
        SessionHelper::forgetSessionForMismatchURL('*trading/*', $this->refURLCommonKey(), '*purchase_invoice/*');

        return view('trading.purchase_orders.create_edit', $this->service->edit($purchase_order));
    }

    /**
     * 仕入伝票更新
     *
     * @param PurchaseOrderEditRequest $request
     * @param PurchaseOrder $purchase_order
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(PurchaseOrderEditRequest $request, PurchaseOrder $purchase_order): RedirectResponse
    {
        [$error_flag, $message] = $this->service->update($request, $purchase_order);

        // 仕入一覧画面へリダイレクト
        return redirect(Session::get('reference_url.common_key'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 仕入伝票削除
     *
     * @param PurchaseOrder $purchase_order
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(PurchaseOrder $purchase_order): RedirectResponse
    {
        [$error_flag, $message] = $this->service->destroy($purchase_order);

        // 仕入一覧画面へリダイレクト
        return redirect(route('trading.purchase_orders.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 仕入毎の単価を返す（登録されていない場合はマスタの単価を返す）
     *
     * @param Request $request
     * @return JsonResponse$request
     */
    public function getSupplierUnitPrice(Request $request): JsonResponse
    {
        if (is_null($request->input('product_id'))) {
            return response()->json([0]);
        }

        $supplier_id = intval($request->input('supplier_id'));
        $product_id = intval($request->input('product_id'));
        $unit_name = $request->input('unit_name') ?? '';

        // 得意先毎の単価を返す（登録されていない場合はマスタの単価を返す）
        return ProductHelper::getSupplierUnitPrice($supplier_id, $product_id, $unit_name);
    }

    /**
     * 仕入先・商品別毎の単価履歴を返す
     *
     * @param Request $request
     * @return JsonResponse $request
     */
    public function getSupplierUnitPriceHistory(Request $request): JsonResponse
    {

        $supplier_id = intval($request->input('supplier_id'));
        $product_id = intval($request->input('product_id'));
        $count = intval($request->input('count')) ?? 0;

        if (empty($supplier_id) || empty($product_id)) {
            // 仕入先・商品が特定できない場合は空を返す
            return response()->json([]);
        }

        // 仕入先・商品別毎の単価履歴を返す
        return PurchaseOrderHelper::getPurchaseUnitPriceHistory($supplier_id, $product_id, $count, true);
    }
}
