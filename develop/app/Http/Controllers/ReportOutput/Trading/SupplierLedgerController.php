<?php

/**
 * 仕入先元帳(締間)軽減画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput\Trading;

use App\Consts\SessionConst;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\Trading\SupplierLedgerSearchRequest;
use App\Models\Master\MasterSupplier;
use App\Services\Excel\SupplierLedgerExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class SupplierLedgerController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * SupplierLedgerController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application report output.
     *
     * @return View
     */
    public function index(SupplierLedgerSearchRequest $request)
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.supplier_ledger'),
                'next_url' => route('report_output.trading.supplier_ledger.index'),
                'download_excel_url' => route('report_output.trading.supplier_ledger.download_excel'),
                'download_pdf_url' => route('report_output.trading.supplier_ledger.download_pdf'),
            ],
            /** 検索項目 */
            'search_items' => [
                /** 仕入先データ */
                'suppliers' => MasterSupplier::query()->oldest('name_kana')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** デフォルト値 */
            'default_max_date' => config('consts.default.common.default_max_date'),
            'default_max_month' => config('consts.default.common.default_max_month'),
        ];

        return view('report_output.trading.supplier_ledger.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param SupplierLedgerSearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(SupplierLedgerSearchRequest $request): mixed
    {
        $excelService = new SupplierLedgerExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        if (empty($outputData) || empty($outputData['ledger_data'])) {
            // 印刷対象データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('report_output.trading.supplier_ledger.index'))
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
     * @param SupplierLedgerSearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(SupplierLedgerSearchRequest $request): mixed
    {
        $excelService = new SupplierLedgerExcelService();

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
