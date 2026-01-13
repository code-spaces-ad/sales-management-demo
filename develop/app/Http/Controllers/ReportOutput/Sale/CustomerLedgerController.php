<?php

/**
 * 得意先元帳画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput\Sale;

use App\Consts\SessionConst;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\Sale\CustomerLedgerSearchRequest;
use App\Models\Master\MasterCustomer;
use App\Services\Excel\CustomerLedgerExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class CustomerLedgerController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * CustomerLedgerController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application report output.
     *
     * @param CustomerLedgerSearchRequest $request
     * @return View
     */
    public function index(CustomerLedgerSearchRequest $request)
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.customer_ledger'),
                'next_url' => route('report_output.sale.customer_ledger.index'),
                'download_excel_url' => route('report_output.sale.customer_ledger.download_excel'),
                'download_pdf_url' => route('report_output.sale.customer_ledger.download_pdf'),
            ],
            /** 検索項目 */
            'search_items' => [
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('sort_code')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** デフォルト値 */
            'default_max_date' => config('consts.default.common.default_max_date'),
            'default_max_month' => config('consts.default.common.default_max_month'),
        ];

        return view('report_output.sale.customer_ledger.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param CustomerLedgerSearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(CustomerLedgerSearchRequest $request): mixed
    {
        $excelService = new CustomerLedgerExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        if (empty($outputData) || empty($outputData['ledger_data'])) {
            // 印刷対象データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('report_output.sale.customer_ledger.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true])
                ->withInput($searchConditions);
        }

        // スプレッドシート作成
        $excelService->getSpreadSheet($searchConditions, $outputData);

        return $excelService->downloadExcel();
    }

    /**
     * PDFダウンロード
     *
     * @param CustomerLedgerSearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(CustomerLedgerSearchRequest $request): mixed
    {
        $excelService = new CustomerLedgerExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        if (empty($outputData) || empty($outputData['ledger_data'])) {
            // 印刷対象データ 0件の場合
            return config('consts.message.error.E0000001');
        }

        // スプレッドシート作成
        $excelService->getSpreadSheet($searchConditions, $outputData, 1);

        try {
            $pdfPath = $excelService->makePdf();
        } catch (Exception $e) {
            LogHelper::report($e, config('consts.message.error.E0000002'));

            return config('consts.message.error.E0000002');
        }

        return $pdfPath;
    }
}
