<?php

/**
 * 支払締処理画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\PurchaseInvoice;

use App\Consts\SessionConst;
use App\Helpers\ClosingDateHelper;
use App\Helpers\LedgerStocksHelper;
use App\Helpers\MessageHelper;
use App\Helpers\PurchaseClosingHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseInvoice\PurchaseClosingCancelRequest;
use App\Http\Requests\PurchaseInvoice\PurchaseClosingSearchRequest;
use App\Http\Requests\PurchaseInvoice\PurchaseClosingStoreRequest;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterSupplier;
use App\Models\Trading\PurchaseOrder;
use App\Services\PurchaseClosingService;
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
 * 支払締処理画面用コントローラー
 */
class PurchaseClosingController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * Show the application dashboard.
     *
     * @param PurchaseClosingSearchRequest $request
     * @return View
     */
    public function index(PurchaseClosingSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        // 締年月日の範囲年月取得
        [$purchase_date_start, $purchase_date_end] = ClosingDateHelper::getChargeCloseTermDate(
            $search_condition_input_data['purchase_date'], $search_condition_input_data['closing_date']);

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 仕入先データ */
                'suppliers' => MasterSupplier::getClosingBillingSupplier(),
                /** 支払締日 */
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
                'purchase_closing_date_display' => ClosingDateHelper::getChargeClosingDateDisplay(
                    $purchase_date_end,
                    $search_condition_input_data['closing_date']
                ),
                'purchase_date_start' => $purchase_date_start,
                'purchase_date_end' => $purchase_date_end,
                /** 支払締対象データ */
                'purchase_data' => PurchaseOrder::getTargetClosingSupplierData(
                    $search_condition_input_data,
                    $purchase_date_start, $purchase_date_end
                ),
            ],
        ];

        return view('purchase_invoice.purchase_closing.index', $data);
    }

    /**
     * 締処理
     *
     * @param purchaseClosingStoreRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(purchaseClosingStoreRequest $request): RedirectResponse
    {
        $supplier_ids = explode(',', $request->input('supplier_ids'));
        $purchase_date = $request->input('purchase_date');
        $closing_date = $request->input('closing_date');

        // 締処理実行
        $result = $this->closingProcess($supplier_ids, $purchase_date, $closing_date, $request->input('department_id'), $request->input('office_facility_id'));
        [$message, $error_flag] = MessageHelper::getPurchaseOrderClosingStoreMessage(
            json_decode($result->getContent(), true));

        // 帳簿在庫数登録
        LedgerStocksHelper::registLedgerStock($purchase_date);

        // 一覧画面へリダイレクト
        return redirect(route('purchase_invoice.purchase_closing.index',
            Session::get('search_condition_input_data.purchase_closing', [])))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 締処理解除
     *
     * @param purchaseClosingCancelRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function cancel(purchaseClosingCancelRequest $request): RedirectResponse
    {
        $purchase_data_ids = explode(',', $request->input('purchase_data_ids'));
        // 締処理解除実行
        $result = $this->cancelProcess($purchase_data_ids);
        [$message, $error_flag] = MessageHelper::getPurchaseOrderClosingCancelMessage(
            json_decode($result->getContent(), true));

        // 一覧画面へリダイレクト
        return redirect(route('purchase_invoice.purchase_closing.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 締処理
     *
     * @param array $supplier_ids
     * @param string $purchase_date
     * @param int $closing_date
     * @param int $department_id
     * @param int $office_facilities_id
     * @return JsonResponse
     */
    private function closingProcess(array $supplier_ids, string $purchase_date, int $closing_date, int $department_id, int $office_facilities_id): JsonResponse
    {
        $success_count = 0;
        $skip_count = 0;
        $failed_count = 0;
        // 締処理
        foreach ($supplier_ids as $supplier_id) {
            $purchaseClosingService = new PurchaseClosingService();
            try {
                $result = $purchaseClosingService->setSupplierId($supplier_id)
                    ->setClosingDate($purchase_date, $closing_date)
                    ->setDepartmentAndOfficeFacilitiesId($department_id, $office_facilities_id)
                    ->closing();

                $content = json_decode($result->content(), true);
                if ($content['message'] === 'success') {
                    ++$success_count;
                }
                if ($content['message'] === 'skip') {
                    ++$skip_count;
                }
                if ($content['message'] === 'failed') {
                    ++$failed_count;
                }

            } catch (Exception $e) {
                Log::error($e->getMessage());
                ++$failed_count;
            }
        }

        $json = [
            'success' => $success_count,
            'skip' => $skip_count,
            'failed' => $failed_count,
        ];

        return response()->json($json);
    }

    /**
     * 締処理解除
     *
     * @param array $purchase_data_ids
     * @return JsonResponse
     */
    private function cancelProcess(array $purchase_data_ids): JsonResponse
    {
        $success_count = 0;
        $failed_count = 0;

        // 締処理解除
        foreach ($purchase_data_ids as $purchase_data_id) {
            $purchaseClosingService = new PurchaseClosingService();
            try {
                $result = $purchaseClosingService->setPurchaseDataId($purchase_data_id)->cancel();

                $content = json_decode($result->content(), true);
                if ($content['message'] === 'success') {
                    ++$success_count;
                }
            } catch (Exception $e) {
                $res = print_r($e->getMessage());
                echo $res;

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
     * 指定の仕入先・年月(未来含む)の仕入締処理状況を返す
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function isClosing(Request $request): JsonResponse
    {
        $supplier_id = $request->input('supplier_id');
        $closing_date = $request->input('closing_date');

        return response()->json(
            [
                PurchaseClosingHelper::getPurchaseClosing($supplier_id, (new Carbon($closing_date))),
            ]
        );
    }
}
