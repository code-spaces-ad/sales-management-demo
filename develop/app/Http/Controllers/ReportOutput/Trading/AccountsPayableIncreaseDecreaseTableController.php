<?php

/**
 * 買掛金増減表画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput\Trading;

use App\Consts\SessionConst;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\Trading\AccountsPayableIncreaseDecreaseTableSearchRequest;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Services\Excel\AccountsPayableIncreaseDecreaseTableExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class AccountsPayableIncreaseDecreaseTableController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * AccountsPayableIncreaseDecreaseTableController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application report output.
     *
     * @param AccountsPayableIncreaseDecreaseTableSearchRequest $request
     * @return View
     */
    public function index(AccountsPayableIncreaseDecreaseTableSearchRequest $request)
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.accounts_payable_increase_decrease_table'),
                'next_url' => route('report_output.trading.accounts_payable_increase_decrease_table.index'),
                'download_excel_url' => route('report_output.trading.accounts_payable_increase_decrease_table.download_excel'),
                'download_pdf_url' => route('report_output.trading.accounts_payable_increase_decrease_table.download_pdf'),
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

        return view('report_output.trading.accounts_payable_increase_decrease_table.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param AccountsPayableIncreaseDecreaseTableSearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(AccountsPayableIncreaseDecreaseTableSearchRequest $request): mixed
    {
        $excelService = new AccountsPayableIncreaseDecreaseTableExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        if (empty($outputData)) {
            // 印刷対象データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('report_output.trading.accounts_payable_increase_decrease_table.index'))
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
     * @param AccountsPayableIncreaseDecreaseTableSearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(AccountsPayableIncreaseDecreaseTableSearchRequest $request): mixed
    {
        $excelService = new AccountsPayableIncreaseDecreaseTableExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        if (empty($outputData)) {
            // 印刷対象データ 0件の場合
            return config('consts.message.error.E0000001');
        }

        // スプレッドシート作成
        $excelService->getSpreadSheet($searchConditions, $outputData, null, 1);

        try {
            $pdfPath = $excelService->makePdf();
        } catch (Exception $e) {
            LogHelper::report($e, config('consts.message.error.E0000002'));

            return config('consts.message.error.E0000002');
        }

        return $pdfPath;
    }
}
