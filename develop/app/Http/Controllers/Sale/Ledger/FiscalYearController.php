<?php

/**
 * 年度別販売実績表画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Sale\Ledger;

use App\Consts\SessionConst;
use App\Helpers\ExcelHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\Ledger\FiscalYearRequest;
use App\Services\Sale\Ledger\FiscalYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FiscalYearController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    protected FiscalYearService $service;

    /**
     * FiscalYearController constructor.
     */
    public function __construct(FiscalYearService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Show the application dashboard.
     *
     * @param FiscalYearRequest $request
     * @return View
     */
    public function index(FiscalYearRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $conditions = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        return view('sale.ledger.fiscal_year.index', $this->service->index($conditions));
    }

    /**
     * Excelダウンロード
     *
     * @param FiscalYearRequest $request
     * @return StreamedResponse
     *
     * @throws Exception
     */
    public function downloadExcel(FiscalYearRequest $request): StreamedResponse
    {
        return ExcelHelper::outputExcel(
            $this->service->getSpreadSheet($request),
            now()->format('YmdHis') . '_' . config('consts.excel.filename.ledger_fiscal_year')
        );
    }

    /**
     * PDF表示（Excel -> PDF変換）
     *
     * @param FiscalYearRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function showPdf(FiscalYearRequest $request): RedirectResponse
    {
        [$pdf_file_name, $ret] = $this->service->showPdf($request);

        if ($ret) {
            // 年度別販売実績表画面にリダイレクト
            return redirect(route('sale.ledger.fiscal_year'))
                ->with([
                    'message' => config('consts.message.common.show_pdf_failed'),
                    'error_flag' => true,
                ]);
        }

        // PDFファイルURLにリダイレクト
        return redirect(asset('/') . config('consts.pdf.temp_path') . $pdf_file_name);
    }
}
