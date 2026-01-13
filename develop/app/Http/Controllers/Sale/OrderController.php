<?php

/**
 * 売上管理用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Sale;

use App\Helpers\PdfHelper;
use App\Helpers\ProductHelper;
use App\Helpers\SalesOrderHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Sale\OrderEditRequest;
use App\Http\Requests\Sale\OrderSearchRequest;
use App\Models\Sale\SalesOrder;
use App\Services\Excel\OrderExcelService;
use App\Services\Sale\OrderService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 売上管理用コントローラー
 */
class OrderController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    protected OrderService $service;

    /**
     * OrderServiceをインスタンス
     *
     * @param OrderService $service
     */
    public function __construct(OrderService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * 売上伝票一覧画面
     *
     * @param OrderSearchRequest $request
     * @return View
     */
    public function index(OrderSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        if (strlen(Session::get('pdf')) > 0) {
            // PDFファイルを別タブで開く
            echo "<script>window.open('" . Session::get('pdf') . "', '_blank')</script>";
        }

        Session::forget('pdf');

        return view('sale.orders.index', $this->service->index($search_condition_input_data));
    }

    /**
     * 売上伝票新規登録画面
     *
     * @return View
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(): View
    {
        $target_record_data = new SalesOrder();
        $select_order_id = Session::get('select_order_id');

        if ($select_order_id != '') {
            // 選択した伝票をコピー
            $target_record_data = SalesOrder::find($select_order_id);
            Session::put('select_order_id', '');
        }

        if (strlen(Session::get('pdf')) > 0) {
            // PDFファイルを別タブで開く
            echo "<script>window.open('" . Session::get('pdf') . "', '_blank')</script>";
        }

        Session::forget('pdf');
        SessionHelper::forgetSessionForMismatchURL('*sale/orders*', $this->refURLCommonKey());

        return view('sale.orders.create_edit', $this->service->create($target_record_data));
    }

    /**
     * 売上伝票新規登録・複製
     *
     * @param OrderEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(OrderEditRequest $request): RedirectResponse
    {
        [$error_flag, $message] = $this->service->store($request);

        // 複製時は一覧画面へ、新規登録時は新規登録画面へ
        $redirectRoute = null;
        if ($request->input('copy_number', '0') !== '0') {
            $redirectRoute = route('sale.orders.index');
        }
        if ($request->input('copy_number', '0') === '0') {
            $redirectRoute = route('sale.orders.create');
        }

        return redirect($redirectRoute)
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OrderEditRequest $request
     * @return Application|RedirectResponse|Redirector
     *
     * @throws Exception
     */
    public function storeAndShowPdf(OrderEditRequest $request)
    {
        $pdf = '';
        [$error_flag, $message] = $this->service->store($request);
        if (!$error_flag) {
            $request->id = SalesOrder::max('id');
            $pdf = $this->showPdfAndRegist($request);
        }

        // 新規登録画面へリダイレクト
        return redirect(route('sale.orders.create'))
            ->with(['message' => $message, 'error_flag' => $error_flag, 'pdf' => $pdf]);
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

        return redirect(route('sale.orders.create'));
    }

    /**
     * 売上伝票編集画面
     *
     * @param SalesOrder $order
     * @return View
     */
    public function edit(SalesOrder $order): View
    {
        SessionHelper::forgetSessionForMismatchURL('*sale/*', $this->refURLCommonKey(), '*charge_detail/*');

        return view('sale.orders.create_edit', $this->service->edit($order));
    }

    /**
     * 売上伝票更新
     *
     * @param OrderEditRequest $request
     * @param SalesOrder $order
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(OrderEditRequest $request, SalesOrder $order): RedirectResponse
    {
        [$error_flag, $message] = $this->service->update($request, $order);

        // 売上伝票一覧画面へリダイレクト
        return redirect(Session::get('reference_url.common_key'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OrderEditRequest $request
     * @param SalesOrder $order
     * @return Application|RedirectResponse|Redirector
     *
     * @throws Exception
     */
    public function updateAndShowPdf(OrderEditRequest $request, SalesOrder $order)
    {
        $pdf = '';
        [$error_flag, $message] = $this->service->update($request, $order);
        if (!$error_flag) {
            $request->id = $order->id;
            $pdf = $this->showPdfAndRegist($request);
        }

        // 売上伝票一覧画面へリダイレクト
        return redirect(route('sale.orders.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag, 'pdf' => $pdf]);
    }

    /**
     * 売上伝票削除
     *
     * @param SalesOrder $order
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(SalesOrder $order): RedirectResponse
    {
        [$error_flag, $message] = $this->service->destroy($order);

        // 売上伝票一覧画面へリダイレクト
        return redirect(route('sale.orders.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 納品書 Excelダウンロード
     *
     * @param Request $request
     * @return StreamedResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function downloadExcel(Request $request): StreamedResponse
    {
        $excel_service = new OrderExcelService();
        $spreadsheet = $excel_service->getSpreadSheet($request);

        // Excelファイル名
        $filename = Carbon::now()->format('YmdHis') . '_' . config('consts.excel.filename.sale_delivery_slip_print');

        // Output
        $callback = function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        };

        $status = 200;
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment;filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ];

        return new StreamedResponse($callback, $status, $headers);
    }

    /**
     * 納品書 PDF表示（Excel -> PDF変換）
     *
     * @param Request $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function showPdf(Request $request): RedirectResponse
    {
        $pdf_file_name = $this->createPdf($request);

        // PDFファイルURLにリダイレクト
        return redirect($pdf_file_name);
    }

    /**
     * 納品書 PDF表示（Excel -> PDF変換）
     *
     * @param Request $request
     * @return Application|RedirectResponse|Redirector|string
     *
     * @throws Exception
     */
    public function showPdfAndRegist(Request $request)
    {
        return $this->createPdf($request);
    }

    /**
     * 納品書 PDF表示（Excel -> PDF変換）
     *
     * @param Request $request
     * @return Application|RedirectResponse|Redirector|string
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function createPdf(Request $request)
    {
        $excel_service = new OrderExcelService();
        $spreadsheet = $excel_service->getSpreadSheet($request);

        $user_id = Auth::user()->id_zerofill ?? '0000000000';
        $file_name = date('YmdHis');
        $excel_file_name = "{$file_name}_$user_id.xlsx";
        $pdf_file_name = "{$file_name}_$user_id.pdf";

        // 一旦、Excelファイルを保存
        $excel_path = storage_path(config('consts.excel.temp_path')) . $excel_file_name;
        $writer = new Xlsx($spreadsheet);
        $writer->save($excel_path);

        // Excel -> PDF 変換
        $pdf_dir = public_path(config('consts.pdf.temp_path'));
        $ret = PdfHelper::convertPdf($excel_path, $pdf_dir);
        if ($ret !== 0) {
            $message = config('consts.message.common.show_pdf_failed');

            // 売上伝票入力画面にリダイレクト
            return redirect(route('sale.orders.edit', $request->id))
                ->with(['message' => $message, 'error_flag' => true]);
        }

        SalesOrder::updatePrintingDate($request);

        return asset('/') . config('consts.pdf.temp_path') . $pdf_file_name;
    }

    /**
     * 得意先毎の単価を返す（登録されていない場合はマスタの単価を返す）
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomerPrice(Request $request): JsonResponse
    {
        if (is_null($request->input('product_id'))) {
            return response()->json([0]);
        }

        $customer_id = intval($request->input('customer_id'));
        $product_id = intval($request->input('product_id'));

        // 得意先毎の単価を返す（登録されていない場合はマスタの単価を返す）
        return ProductHelper::getCustomerPrice($customer_id, $product_id);
    }

    /**
     * 得意先毎の単価を返す（登録されていない場合はマスタの単価を返す）
     * ※CodeSpacesでは使用しない
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomerUnitPrice(Request $request): JsonResponse
    {
        if (is_null($request->input('product_id'))) {
            return response()->json([0]);
        }

        $customer_id = intval($request->input('customer_id'));
        $product_id = intval($request->input('product_id'));
        $unit_name = $request->input('unit_name') ?? '';

        // 得意先毎の単価を返す（登録されていない場合はマスタの単価を返す）
        return ProductHelper::getCustomerUnitPrice($customer_id, $product_id, $unit_name);
    }

    /**
     * 得意先・商品毎の単価履歴を返す
     *
     * @param Request $request
     * @return JsonResponse $request
     */
    public function getCustomerUnitPriceHistory(Request $request): JsonResponse
    {
        $customer_id = intval($request->input('customer_id'));
        $product_id = intval($request->input('product_id'));
        $count = intval($request->input('count')) ?? 0;

        if (empty($customer_id) || empty($product_id)) {
            // 仕入先・商品が特定できない場合は空を返す
            return response()->json([]);
        }

        // 仕入先・商品別毎の単価履歴を返す
        return SalesOrderHelper::getSalesUnitPriceHistory($customer_id, $product_id, $count, true);
    }
}
