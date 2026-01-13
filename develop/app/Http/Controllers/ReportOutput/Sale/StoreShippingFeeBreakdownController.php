<?php

/**
 * 各店送料内訳画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput\Sale;

use App\Consts\SessionConst;
use App\Helpers\DateHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\Sale\StoreShippingFeeBreakdownSearchRequest;
use App\Services\Excel\StoreShippingFeeBreakdownExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class StoreShippingFeeBreakdownController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * StoreShippingFeeBreakdownController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application report output.
     *
     * @param StoreShippingFeeBreakdownSearchRequest $request
     * @return View
     */
    public function index(StoreShippingFeeBreakdownSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.store_shipping_fee_breakdown'),
                'next_url' => route('report_output.sale.store_shipping_fee_breakdown.index'),
                'download_excel_url' => route('report_output.sale.store_shipping_fee_breakdown.download_excel'),
                'download_pdf_url' => route('report_output.sale.store_shipping_fee_breakdown.download_pdf'),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
        ];

        return view('report_output.sale.store_shipping_fee_breakdown.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param StoreShippingFeeBreakdownSearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(StoreShippingFeeBreakdownSearchRequest $request): mixed
    {
        $excelService = new StoreShippingFeeBreakdownExcelService();

        // データ取得
        $searchConditions = $request->validated();
        $searchConditions['start_date'] = DateHelper::getMonthStart($searchConditions['year_month']);
        $searchConditions['end_date'] = DateHelper::getMonthEnd($searchConditions['year_month']);

        $outputData = $excelService->getOutputData($searchConditions);
        if (empty($outputData)) {
            // 印刷対象データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('report_output.sale.store_shipping_fee_breakdown.index'))
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
     * @param StoreShippingFeeBreakdownSearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(StoreShippingFeeBreakdownSearchRequest $request): mixed
    {
        $excelService = new StoreShippingFeeBreakdownExcelService();

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
