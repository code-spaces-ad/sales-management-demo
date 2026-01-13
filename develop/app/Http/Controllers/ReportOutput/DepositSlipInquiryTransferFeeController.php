<?php

/**
 * 入金伝票問い合わせ振込手数料画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput;

use App\Consts\SessionConst;
use App\Helpers\DateHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\DepositSlipInquiryTransferFeeSearchRequest;
use App\Services\Excel\DepositSlipInquiryTransferFeeExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class DepositSlipInquiryTransferFeeController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * DepositSlipInquiryTransferFeeController constructor.
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
    public function index(DepositSlipInquiryTransferFeeSearchRequest $request)
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.deposit_slip_inquiry_transfer_fee'),
                'next_url' => route('report_output.deposit_slip_inquiry_transfer_fee.index'),
                'download_excel_url' => route('report_output.deposit_slip_inquiry_transfer_fee.download_excel'),
                'download_pdf_url' => route('report_output.deposit_slip_inquiry_transfer_fee.download_pdf'),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
        ];

        return view('report_output.deposit_slip_inquiry_transfer_fee.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param DepositSlipInquiryTransferFeeSearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(DepositSlipInquiryTransferFeeSearchRequest $request): mixed
    {
        $excelService = new DepositSlipInquiryTransferFeeExcelService();

        // データ取得
        $searchConditions = $request->validated();
        $searchConditions['start_date'] = DateHelper::getMonthStart($searchConditions['payment_date']['start']);
        $searchConditions['end_date'] = DateHelper::getMonthEnd($searchConditions['payment_date']['end']);

        $outputData = $excelService->getOutputData($searchConditions);
        if (empty($outputData)) {
            // 印刷対象データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('report_output.deposit_slip_inquiry_transfer_fee.index'))
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
     * @param DepositSlipInquiryTransferFeeSearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(DepositSlipInquiryTransferFeeSearchRequest $request): mixed
    {
        $excelService = new DepositSlipInquiryTransferFeeExcelService();

        // データ取得
        $searchConditions = $request->validated();
        $searchConditions['start_date'] = DateHelper::getMonthStart($searchConditions['payment_date']['start']);
        $searchConditions['end_date'] = DateHelper::getMonthEnd($searchConditions['payment_date']['end']);

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
