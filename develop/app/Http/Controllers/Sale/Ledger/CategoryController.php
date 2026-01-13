<?php

/**
 * 種別累計売上表画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Sale\Ledger;

use App\Consts\SessionConst;
use App\Helpers\ExcelHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\Ledger\CategorySearchRequest;
use App\Services\Sale\Ledger\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    protected CategoryService $service;

    /**
     * CategoryController constructor.
     */
    public function __construct(CategoryService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Show the application dashboard.
     *
     * @param CategorySearchRequest $request
     * @return View
     */
    public function index(CategorySearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $conditions = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        return view('sale.ledger.categories.index', $this->service->index($conditions));
    }

    /**
     * Excelダウンロード
     *
     * @param CategorySearchRequest $request
     * @return StreamedResponse
     *
     * @throws Exception
     */
    public function downloadExcel(CategorySearchRequest $request): StreamedResponse
    {
        return ExcelHelper::outputExcel(
            $this->service->getSpreadSheet($request),
            now()->format('YmdHis') . '_' . config('consts.excel.filename.ledger_category')
        );
    }

    /**
     * PDF表示（Excel -> PDF変換）
     *
     * @param CategorySearchRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function showPdf(CategorySearchRequest $request): RedirectResponse
    {
        [$pdf_file_name, $ret] = $this->service->showPdf($request);

        if ($ret) {
            // 種別累計売上表画面にリダイレクト
            return redirect(route('sale.ledger.categories'))
                ->with([
                    'message' => config('consts.message.common.show_pdf_failed'),
                    'error_flag' => true,
                ]);
        }

        // PDFファイルURLにリダイレクト
        return redirect(asset('/') . config('consts.pdf.temp_path') . $pdf_file_name);
    }
}
