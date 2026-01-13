<?php

/**
 * 経費コード別買掛金一覧画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput\Trading;

use App\Consts\SessionConst;
use App\Helpers\DateHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\Trading\AccountsPayableListByExpenseCodeSearchRequest;
use App\Services\Excel\AccountsPayableListByExpenseCodeExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class AccountsPayableListByExpenseCodeController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * AccountsPayableListByExpenseCodeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application report output.
     *
     * @param AccountsPayableListByExpenseCodeSearchRequest $request
     * @return View
     */
    public function index(AccountsPayableListByExpenseCodeSearchRequest $request)
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.accounts_payable_list_by_expense_code'),
                'next_url' => route('report_output.trading.accounts_payable_list_by_expense_code.index'),
                'download_excel_url' => route('report_output.trading.accounts_payable_list_by_expense_code.download_excel'),
                'download_pdf_url' => route('report_output.trading.accounts_payable_list_by_expense_code.download_pdf'),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
        ];

        return view('report_output.trading.accounts_payable_list_by_expense_code.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param AccountsPayableListByExpenseCodeSearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(AccountsPayableListByExpenseCodeSearchRequest $request): mixed
    {
        $excelService = new AccountsPayableListByExpenseCodeExcelService();

        // データ取得
        $searchConditions = $request->validated();
        $searchConditions['start_date'] = DateHelper::getMonthStart($searchConditions['year_month']);
        $searchConditions['end_date'] = DateHelper::getMonthEnd($searchConditions['year_month']);

        $outputData = $excelService->getOutputData($searchConditions);
        if (empty($outputData)) {
            // 印刷対象データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('report_output.trading.accounts_payable_list_by_expense_code.index'))
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
     * @param AccountsPayableListByExpenseCodeSearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(AccountsPayableListByExpenseCodeSearchRequest $request): mixed
    {
        $excelService = new AccountsPayableListByExpenseCodeExcelService();

        // データ取得
        $searchConditions = $request->validated();
        $searchConditions['start_date'] = DateHelper::getMonthStart($searchConditions['year_month']);
        $searchConditions['end_date'] = DateHelper::getMonthEnd($searchConditions['year_month']);

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
