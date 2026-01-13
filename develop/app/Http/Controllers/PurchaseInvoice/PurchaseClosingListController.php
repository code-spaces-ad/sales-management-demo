<?php

/**
 * 仕入締一覧画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\PurchaseInvoice;

use App\Consts\SessionConst;
use App\Helpers\ClosingDateHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseInvoice\PurchaseClosingListSearchRequest;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterSupplier;
use App\Models\PurchaseInvoice\PurchaseClosing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 仕入締一覧画面用コントローラー
 */
class PurchaseClosingListController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * PurchaseClosingListController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param PurchaseClosingListSearchRequest $request
     * @return View
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function index(PurchaseClosingListSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $purchase_closing_total = PurchaseClosing::getSearchResultTotal($search_condition_input_data);
        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 得意先データ */
                'suppliers' => MasterSupplier::get(),
                /** 仕入締締日 */
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
                /** 仕入締データ */
                'purchase_closing_list_data' => PurchaseClosing::getSearchResult($search_condition_input_data),
                'purchase_closing_total' => $purchase_closing_total[0],
                /** 共通使用セッションキー(URL) */
                'session_common_key' => $this->refURLCommonKey(),
            ],
        ];

        return view('purchase_invoice.purchase_closing_list.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param PurchaseClosingListSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(PurchaseClosingListSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.purchase_closing_list');

        $headings = [
            '仕入先',
            '前回仕入額',
            '今回支払額',
            '繰越残高',
            '今回仕入額',
            '消費税',
            '今回総仕入額',
        ];

        $purchase_closing = PurchaseClosing::getPurchaseClosingListResult($search_condition_input_data);
        $filters = [

            function ($purchase_closing) {
                return $purchase_closing->supplier_name;
            },
            function ($purchase_closing) {
                return number_format($purchase_closing->before_purchase_total);
            },
            function ($purchase_closing) {
                return number_format($purchase_closing->payment_total);
            },
            function ($purchase_closing) {
                return number_format($purchase_closing->carryover);
            },
            function ($purchase_closing) {
                return number_format($purchase_closing->purchase_total);
            },
            function ($purchase_closing) {
                return number_format($purchase_closing->purchase_tax_total);
            },
            function ($purchase_closing) {
                return number_format($purchase_closing->purchase_closing_total);
            },
        ];

        if ($purchase_closing->isEmpty()) {
            // 仕入締データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('purchase_invoice.purchase_closing_list.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $purchase_closing->exportExcel($filename, $headings, $filters);
    }
}
