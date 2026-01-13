<?php

/**
 * 売掛金仕訳帳CSV出力軽減画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput;

use App\Consts\SessionConst;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\AccountsReceivableJournalSearchRequest;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Services\Excel\AccountsReceivableJournalExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class AccountsReceivableJournalController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * AccountsReceivableJournalController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application report output.
     *
     * @param AccountsReceivableJournalSearchRequest $request
     * @return View
     */
    public function index(AccountsReceivableJournalSearchRequest $request)
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.accounts_receivable_journal'),
                'next_url' => route('report_output.accounts_receivable_journal.index'),
                'download_excel_url' => route('report_output.accounts_receivable_journal.download_excel'),
                'download_pdf_url' => route('report_output.accounts_receivable_journal.download_pdf'),
            ],
            /** 検索項目 */
            'search_items' => [
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 支所データ */
                'office_facilities' => MasterOfficeFacility::query()->where('department_id', 3)->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** デフォルト値 */
            'default_max_date' => config('consts.default.common.default_max_date'),
            'default_max_month' => config('consts.default.common.default_max_month'),
        ];

        return view('report_output.accounts_receivable_journal.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param AccountsReceivableJournalSearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(AccountsReceivableJournalSearchRequest $request): mixed
    {
        $excelService = new AccountsReceivableJournalExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        if (empty($outputData)) {
            // 印刷対象データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('report_output.accounts_receivable_journal.index'))
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
     * @param AccountsReceivableJournalSearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(AccountsReceivableJournalSearchRequest $request): mixed
    {
        $excelService = new AccountsReceivableJournalExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        if (empty($outputData)) {
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
