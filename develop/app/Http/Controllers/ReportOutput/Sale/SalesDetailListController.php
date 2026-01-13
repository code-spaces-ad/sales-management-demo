<?php

/**
 * 売上明細一覧(売上日指定)画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput\Sale;

use App\Consts\SessionConst;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\Sale\SalesDetailListSearchRequest;
use App\Services\Excel\SalesDetailListExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class SalesDetailListController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * SalesDetailListController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application report output.
     *
     * @param SalesDetailListSearchRequest $request
     * @return View
     */
    public function index(SalesDetailListSearchRequest $request)
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.sales_detail_list'),
                'next_url' => route('report_output.sale.sales_detail_list.index'),
                'download_excel_url' => route('report_output.sale.sales_detail_list.download_excel'),
                'download_pdf_url' => route('report_output.sale.sales_detail_list.download_pdf'),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
        ];

        return view('report_output.sale.sales_detail_list.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param SalesDetailListSearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(SalesDetailListSearchRequest $request): mixed
    {
        $excelService = new SalesDetailListExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);

        if (empty($outputData)) {
            // 印刷対象データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('report_output.sale.sales_detail_list.index'))
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
     * @param SalesDetailListSearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(SalesDetailListSearchRequest $request): mixed
    {
        $excelService = new SalesDetailListExcelService();

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
