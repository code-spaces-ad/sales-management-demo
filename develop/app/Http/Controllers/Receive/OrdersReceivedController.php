<?php

/**
 * 受注管理用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Receive;

use App\Helpers\PdfHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Receive\OrdersReceivedEditRequest;
use App\Http\Requests\Receive\OrdersReceivedSearchRequest;
use App\Models\Receive\OrdersReceived;
use App\Models\Receive\OrdersReceivedDetail;
use App\Services\Excel\OrderReceivedExcelService;
use App\Services\Excel\OrderReceivedSlipPrintExcelService;
use App\Services\Receive\OrdersReceivedService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 受注管理用コントローラー
 */
class OrdersReceivedController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    protected OrdersReceivedService $service;

    protected OrderReceivedExcelService $excel_service;

    protected OrderReceivedSlipPrintExcelService $slip_print_excel_service;

    /**
     * サービスをインスタンス
     *
     * @param OrdersReceivedService $service
     * @param OrderReceivedExcelService $excel_service
     * @param OrderReceivedSlipPrintExcelService $slip_print_excel_service
     */
    public function __construct(OrdersReceivedService $service,
        OrderReceivedExcelService $excel_service,
        OrderReceivedSlipPrintExcelService $slip_print_excel_service
    ) {
        parent::__construct();

        $this->service = $service;
        $this->excel_service = $excel_service;
        $this->slip_print_excel_service = $slip_print_excel_service;
    }

    /**
     * 受注伝票一覧画面
     *
     * @param OrdersReceivedSearchRequest $request
     * @return View
     */
    public function index(OrdersReceivedSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        if (strlen(Session::get('pdf')) > 0) {
            // PDFファイルを別タブで開く
            echo "<script>window.open('" . Session::get('pdf') . "', '_blank')</script>";
        }

        Session::forget('pdf');

        return view('receive.orders_received.index', $this->service->index($search_condition_input_data));
    }

    /**
     * 受注伝票新規登録画面
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new OrdersReceived();
        $select_order_id = Session::get('select_orders_received_id');
        if ($select_order_id) {
            // 選択した伝票をコピー
            $target_record_data = OrdersReceived::query()->find($select_order_id);
            Session::forget('select_orders_received_id');   // 選択した伝票IDをセッションから削除
        }

        if (strlen(Session::get('pdf')) > 0) {
            // PDFファイルを別タブで開く
            echo "<script>window.open('" . Session::get('pdf') . "', '_blank')</script>";
        }

        // セッションからデータを復元
        $visibility_data = Session::get('visibility_session.orders_received');
        if (!empty($visibility_data)) {
            // 現在のURLとセッション保持時のURLを比較
            $url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            if ($url === $visibility_data['current_url']) {
                // データの復元
                $this->setTargetDataByVisibilityData($visibility_data, $target_record_data);
            }
        }
        // セッション削除
        Session::forget('visibility_session.orders_received');

        Session::forget('pdf');

        SessionHelper::forgetSessionForMismatchURL('*receive/orders_received*', $this->refURLCommonKey());

        return view('receive.orders_received.create_edit', $this->service->create($target_record_data));
    }

    /**
     * 受注伝票新規登録(「売上確定」押下時は売上伝票を作成）
     *
     * @param OrdersReceivedEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(OrdersReceivedEditRequest $request): RedirectResponse
    {
        [$error_flag, $message] = $this->service->store($request);

        // セッション削除
        Session::forget('visibility_session.orders_received');

        // 新規登録画面へリダイレクト
        return redirect(route('receive.orders_received.create'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OrdersReceivedEditRequest $request
     * @return Application|RedirectResponse|Redirector
     *
     * @throws Exception
     */
    public function storeAndShowPdf(OrdersReceivedEditRequest $request)
    {
        $pdf = '';
        [$error_flag, $message] = $this->service->store($request);
        if (!$error_flag) {
            $request->id = OrdersReceived::query()->max('id');
            $pdf = $this->createPdf($request);
        }

        // 新規登録画面へリダイレクト
        return redirect(route('receive.orders_received.create'))
            ->with(['message' => $message, 'error_flag' => $error_flag, 'pdf' => $pdf]);
    }

    /**
     * 受注伝票更新画面へ遷移
     *
     * @param OrdersReceived $orders_received
     * @return View
     */
    public function edit(OrdersReceived $orders_received): View
    {
        // セッションからデータを復元
        $visibility_data = Session::get('visibility_session.orders_received');
        if (!empty($visibility_data)) {
            // 現在のURLとセッション保持時のURLを比較
            $url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            if ($url === $visibility_data['current_url']) {
                // データの復元
                $this->setTargetDataByVisibilityData($visibility_data, $orders_received);
            }
        }
        // セッション削除
        Session::forget('visibility_session.orders_received');

        return view('receive.orders_received.create_edit', $this->service->edit($orders_received));
    }

    /**
     * 受注伝票更新(「売上確定」押下時は売上伝票を作成）
     *
     * @param OrdersReceivedEditRequest $request
     * @param OrdersReceived $orders_received
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(OrdersReceivedEditRequest $request, OrdersReceived $orders_received): RedirectResponse
    {
        [$error_flag, $message] = $this->service->update($request, $orders_received);
        // セッション削除
        Session::forget('visibility_session.orders_received');

        // 編集画面へリダイレクト
        return redirect(Session::get('reference_url.common_key'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OrdersReceivedEditRequest $request
     * @param OrdersReceived $order
     * @return Application|RedirectResponse|Redirector
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function updateAndShowPdf(OrdersReceivedEditRequest $request, OrdersReceived $order)
    {
        $pdf = '';
        [$error_flag, $message] = $this->service->update($request, $order);
        if (!$error_flag) {
            $request->id = $order->id;
            $pdf = $this->createPdf($request);
        }

        // 受注伝票一覧画面へリダイレクト
        return redirect(route('receive.orders_received.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag, 'pdf' => $pdf]);
    }

    /**
     * 受注伝票削除
     *
     * @param OrdersReceived $orders_received
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(OrdersReceived $orders_received): RedirectResponse
    {
        [$error_flag, $message] = $this->service->destroy($orders_received);

        // セッション削除
        Session::forget('visibility_session.orders_received');

        // 受注一覧画面へリダイレクト
        return redirect(route('receive.orders_received.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * セッションデータの復元
     *
     * @param array $visibility_data
     * @param OrdersReceived $editData
     * @return void
     */
    public function setTargetDataByVisibilityData(array $visibility_data, OrdersReceived &$editData)
    {
        // id
        if (!isset($editData['id'])) {
            $editData->setAttribute('id', null);
        }

        // order_number_zerofill
        if (!isset($editData['order_number'])) {
            $editData->setAttribute('order_number', null);
        }

        // order_status
        if (!isset($editData['order_status'])) {
            $editData->setAttribute('order_status', 0);
        }

        // user_name
        if (!isset($editData['user_name'])) {
            $editData->setAttribute('user_name', null);
        }

        // updated_at_slash
        if (!isset($editData['updated_at_slash'])) {
            $editData->setAttribute('updated_at_slash', null);
        }

        $editData->setAttribute('order_date', $visibility_data['order_date']);
        $editData->setAttribute('customer_id', $visibility_data['customer_id']);
        $editData->setAttribute('customer_delivery_id', $visibility_data['customer_delivery_id']);
        $editData->setAttribute('branch_id', $visibility_data['branch_id']);
        $editData->setAttribute('recipient_id', $visibility_data['recipient_id']);
        $editData->setAttribute('employee_id', $visibility_data['employee_id']);

        $detail_count = count($editData['ordersReceivedDetail']);
        for ($i = 0; $i < 5; ++$i) {
            $key = 'detail.' . $i . '.';

            $product_id = $visibility_data[$key . 'product_id'] ?? null;
            $product_name = $visibility_data[$key . 'product_name'] ?? null;
            $delivery_date = $visibility_data[$key . 'delivery_date'] ?? null;
            $warehouse_id = $visibility_data[$key . 'warehouse_id'] ?? null;
            $note = $visibility_data[$key . 'note'] ?? null;
            $sales_confirm = $visibility_data[$key . 'sales_confirm'] ?? null;
            $sort = $visibility_data[$key . 'sort'] ?? null;
            $quantity = $visibility_data[$key . 'quantity'] ?? null;
            if (!is_null($quantity)) {
                $quantity = $quantity . '.0000';
            }

            if ($i < $detail_count) {
                $editData['ordersReceivedDetail'][$i]->setAttribute('product_id', $product_id);
                $editData['ordersReceivedDetail'][$i]->setAttribute('product_name', $product_name);
                $editData['ordersReceivedDetail'][$i]->setAttribute('delivery_date', $delivery_date);
                $editData['ordersReceivedDetail'][$i]->setAttribute('warehouse_id', $warehouse_id);
                $editData['ordersReceivedDetail'][$i]->setAttribute('note', $note);
                $editData['ordersReceivedDetail'][$i]->setAttribute('sales_confirm', $sales_confirm);
                $editData['ordersReceivedDetail'][$i]->setAttribute('sort', $sort);
                $editData['ordersReceivedDetail'][$i]->setAttribute('quantity', $quantity);

                continue;
            }

            $detailData = new OrdersReceivedDetail();
            $detailData->setAttribute('product_id', $product_id);
            $detailData->setAttribute('product_name', $product_name);
            $detailData->setAttribute('delivery_date', $delivery_date);
            $detailData->setAttribute('warehouse_id', $warehouse_id);
            $detailData->setAttribute('note', $note);
            $detailData->setAttribute('sales_confirm', $sales_confirm);
            $detailData->setAttribute('sort', $sort);
            $detailData->setAttribute('quantity', $quantity);

            $editData['ordersReceivedDetail']->add($detailData);
        }
    }

    /**
     * 受注一覧 Excelダウンロード
     *
     * @param OrdersReceivedSearchRequest $request
     * @return StreamedResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function downloadExcel(OrdersReceivedSearchRequest $request): StreamedResponse
    {
        $spreadsheet = $this->excel_service->getSpreadSheet($request);

        // Excelファイル名
        $filename = Carbon::now()->format('YmdHis') . '_' . config('consts.excel.filename.orders_received');

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
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function createPdf(Request $request)
    {
        $spreadsheet = $this->slip_print_excel_service->getSpreadSheet($request);

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

            // 受注伝票入力画面にリダイレクト
            return redirect(route('receive.orders_received.edit', $request->id))
                ->with(['message' => $message, 'error_flag' => true]);
        }

        return asset('/') . config('consts.pdf.temp_path') . $pdf_file_name;
    }
}
