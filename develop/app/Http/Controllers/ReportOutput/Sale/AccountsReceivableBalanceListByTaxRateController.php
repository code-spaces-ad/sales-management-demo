<?php

/**
 * 売掛残高一覧(税率ごと)画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput\Sale;

use App\Consts\SessionConst;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\Sale\AccountsReceivableBalanceListByTaxRateSearchRequest;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Services\Excel\AccountsReceivableBalanceListByTaxRateExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class AccountsReceivableBalanceListByTaxRateController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * AccountsReceivableBalanceListByTaxRateController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application report output.
     *
     * @param AccountsReceivableBalanceListByTaxRateSearchRequest $request
     * @return View
     */
    public function index(AccountsReceivableBalanceListByTaxRateSearchRequest $request)
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.accounts_receivable_balance_list_by_tax_rate'),
                'next_url' => route('report_output.sale.accounts_receivable_balance_list_by_tax_rate.index'),
                'download_excel_url' => route('report_output.sale.accounts_receivable_balance_list_by_tax_rate.download_excel'),
                'download_pdf_url' => route('report_output.sale.accounts_receivable_balance_list_by_tax_rate.download_pdf'),
            ],
            /** 検索項目 */
            'search_items' => [
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 支所データ */
                'office_facilities' => MasterOfficeFacility::query()->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
        ];

        return view('report_output.sale.accounts_receivable_balance_list_by_tax_rate.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param AccountsReceivableBalanceListByTaxRateSearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(AccountsReceivableBalanceListByTaxRateSearchRequest $request): mixed
    {
        $excelService = new AccountsReceivableBalanceListByTaxRateExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        // データがないか、すべての値が0の場合
        if (empty($outputData) || !$excelService->hasValidData($outputData)) {
            // 印刷対象データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('report_output.sale.accounts_receivable_balance_list_by_tax_rate.index'))
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
     * @param AccountsReceivableBalanceListByTaxRateSearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(AccountsReceivableBalanceListByTaxRateSearchRequest $request): mixed
    {
        $excelService = new AccountsReceivableBalanceListByTaxRateExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        // データがないか、すべての値が0の場合
        if (empty($outputData) || !$excelService->hasValidData($outputData)) {
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
