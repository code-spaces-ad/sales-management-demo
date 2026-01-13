<?php

/**
 * 振込手数料画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput\Sale;

use App\Consts\SessionConst;
use App\Helpers\DateHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\Sale\BankTransferFeeSearchRequest;
use App\Services\Excel\BankTransferFeeExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class BankTransferFeeController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * BankTransferFeeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application report output.
     *
     * @param BankTransferFeeSearchRequest $request
     * @return View
     */
    public function index(BankTransferFeeSearchRequest $request)
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.bank_transfer_fee'),
                'next_url' => route('report_output.sale.bank_transfer_fee.index'),
                'download_excel_url' => route('report_output.sale.bank_transfer_fee.download_excel'),
                'download_pdf_url' => route('report_output.sale.bank_transfer_fee.download_pdf'),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
        ];

        return view('report_output.sale.bank_transfer_fee.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param BankTransferFeeSearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(BankTransferFeeSearchRequest $request): mixed
    {
        $excelService = new BankTransferFeeExcelService();

        // データ取得
        $searchConditions = $request->validated();
        $searchConditions['start_date'] = DateHelper::getMonthStart($searchConditions['year_month']);
        $searchConditions['end_date'] = DateHelper::getMonthEnd($searchConditions['year_month']);

        $outputData = $excelService->getOutputData($searchConditions);
        // データがないか、すべての値が0の場合
        if (empty($outputData) || !$excelService->hasValidData($outputData)) {
            // 印刷対象データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('report_output.sale.bank_transfer_fee.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true])
                ->withInput($searchConditions);
        }

        // 設定の並びに変える
        $sortedData = [];
        foreach (SettingsHelper::getReportBankTransferFeeSort() as $code) {
            foreach ($outputData as $key => $data) {

                if ($data->office_facility_code == $code) {
                    $sortedData[] = $data;
                    unset($outputData[$key]);
                    break;
                }
            }
        }

        // 残りがあればセット
        foreach ($outputData as $data) {
            $sortedData[] = $data;
        }

        // スプレッドシート作成
        $excelService->getSpreadSheet($searchConditions, $sortedData);

        return $excelService->downloadExcel();
    }

    /**
     * PDFダウンロード
     *
     * @param BankTransferFeeSearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(BankTransferFeeSearchRequest $request): mixed
    {
        $excelService = new BankTransferFeeExcelService();

        // データ取得
        $searchConditions = $request->validated();
        $searchConditions['start_date'] = DateHelper::getMonthStart($searchConditions['year_month']);
        $searchConditions['end_date'] = DateHelper::getMonthEnd($searchConditions['year_month']);

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
