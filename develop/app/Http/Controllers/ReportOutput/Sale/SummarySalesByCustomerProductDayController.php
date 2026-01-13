<?php

/**
 * 得意先・商品・日別売上集計表画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput\Sale;

use App\Consts\SessionConst;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\Sale\SummarySalesByCustomerProductDaySearchRequest;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterProduct;
use App\Services\Excel\SummarySalesByCustomerProductDayExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class SummarySalesByCustomerProductDayController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * SummarySalesByCustomerProductDayController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application report output.
     *
     * @param SummarySalesByCustomerProductDaySearchRequest $request
     * @return View
     */
    public function index(SummarySalesByCustomerProductDaySearchRequest $request)
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.summary_sales_by_customer_product_day'),
                'next_url' => route('report_output.sale.summary_sales_by_customer_product_day.index'),
                'download_excel_url' => route('report_output.sale.summary_sales_by_customer_product_day.download_excel'),
                'download_pdf_url' => route('report_output.sale.summary_sales_by_customer_product_day.download_pdf'),
            ],
            /** 検索項目 */
            'search_items' => [
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('sort_code')->get(),
                /** 商品マスター */
                'products' => MasterProduct::query()->oldest('name_kana')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
        ];

        return view('report_output.sale.summary_sales_by_customer_product_day.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param SummarySalesByCustomerProductDaySearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(SummarySalesByCustomerProductDaySearchRequest $request): mixed
    {
        $excelService = new SummarySalesByCustomerProductDayExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        if (empty($outputData)) {
            // 印刷対象データ 0件の場合、一覧画面へリダイレクト
            return redirect(route('report_output.sale.summary_sales_by_customer_product_day.index'))
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
     * @param SummarySalesByCustomerProductDaySearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(SummarySalesByCustomerProductDaySearchRequest $request): mixed
    {
        $excelService = new SummarySalesByCustomerProductDayExcelService();

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
