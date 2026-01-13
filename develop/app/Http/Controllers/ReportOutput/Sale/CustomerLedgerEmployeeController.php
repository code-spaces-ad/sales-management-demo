<?php

/**
 * 得意先元帳(担当者別)画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\ReportOutput\Sale;

use App\Consts\SessionConst;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOutput\Sale\CustomerLedgerEmployeeSearchRequest;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterEmployee;
use App\Services\Excel\CustomerLedgerEmployeeExcelService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use PhpOffice\PhpSpreadsheet\Exception;

class CustomerLedgerEmployeeController extends Controller
{
    /** 入力項目トレイト */
    use SessionConst;

    /**
     * CustomerLedgerEmployeeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application report output.
     *
     * @param CustomerLedgerEmployeeSearchRequest $request
     * @return View
     */
    public function index(CustomerLedgerEmployeeSearchRequest $request)
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        $customers = MasterCustomer::query()->oldest('sort_code')->get()->toArray();
        $employees = MasterEmployee::query()->oldest('code')->get()->toArray();

        // 得意先を担当している担当者のIDを取得
        $customerEmployeeIds = array_unique(array_column($customers, 'employee_id'));

        // 得意先を担当している担当者のみをフィルタリング
        $customersEmployee = array_filter($employees, function ($employee) use ($customerEmployeeIds) {
            return in_array($employee['id'], $customerEmployeeIds);
        });

        $data = [
            /** 画面表示項目 */
            'view_settings' => [
                'headline' => config('consts.title.report_output.menu.customer_ledger_by_employee'),
                'next_url' => route('report_output.sale.customer_ledger_by_employee.index'),
                'download_excel_url' => route('report_output.sale.customer_ledger_by_employee.download_excel'),
                'download_pdf_url' => route('report_output.sale.customer_ledger_by_employee.download_pdf'),
            ],
            /** 検索項目 */
            'search_items' => [
                /** 得意先データ */
                'customers' => $customers,
                /** 担当者データ */
                'employees' => array_values($customersEmployee),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** デフォルト値 */
            'default_max_date' => config('consts.default.common.default_max_date'),
            'default_max_month' => config('consts.default.common.default_max_month'),
        ];

        return view('report_output.sale.customer_ledger_by_employee.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param CustomerLedgerEmployeeSearchRequest $request
     * @return Container|mixed|object
     *
     * @throws Exception
     */
    public function downloadExcel(CustomerLedgerEmployeeSearchRequest $request): mixed
    {
        $excelService = new CustomerLedgerEmployeeExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        // データの存在チェック
        if (empty($outputData)) {
            return redirect(route('report_output.sale.customer_ledger_by_employee.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true])
                ->withInput($searchConditions);
        }

        // 複数の得意先の場合の追加チェック
        $validOutputData = [];
        if (isset($outputData[0])) {
            // 得意先が複数件の場合
            foreach ($outputData as $customerData) {
                if (!empty($customerData['ledger_data'])) {
                    $validOutputData[] = $customerData;
                }
            }
        } elseif (isset($outputData['ledger_data']) && !empty($outputData['ledger_data'])) {
            // 得意先が1件の場合
            $validOutputData = [$outputData];
        }

        if (empty($validOutputData)) {
            return redirect(route('report_output.sale.customer_ledger_by_employee.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true])
                ->withInput($searchConditions);
        }

        // スプレッドシート作成
        $excelService->getSpreadSheet($searchConditions, $validOutputData);

        return $excelService->downloadExcel();
    }

    /**
     * PDFダウンロード
     *
     * @param CustomerLedgerEmployeeSearchRequest $request
     * @return Container|mixed|object|string
     *
     * @throws Exception
     */
    public function downloadPdf(CustomerLedgerEmployeeSearchRequest $request): mixed
    {
        $excelService = new CustomerLedgerEmployeeExcelService();

        // データ取得
        $searchConditions = $request->validated();

        $outputData = $excelService->getOutputData($searchConditions);
        // データの存在チェック
        if (empty($outputData)) {
            return config('consts.message.error.E0000001');
        }

        // 複数の得意先の場合の追加チェック
        $validOutputData = [];
        if (isset($outputData[0])) {
            // 得意先が複数件の場合
            foreach ($outputData as $customerData) {
                if (!empty($customerData['ledger_data'])) {
                    $validOutputData[] = $customerData;
                }
            }
        } elseif (isset($outputData['ledger_data']) && !empty($outputData['ledger_data'])) {
            // 得意先が1件の場合
            $validOutputData = [$outputData];
        }

        if (empty($validOutputData)) {
            return config('consts.message.error.E0000001');
        }

        // スプレッドシート作成
        $excelService->getSpreadSheet($searchConditions, $validOutputData, true);

        try {
            $pdfPath = $excelService->makePdf();
        } catch (Exception $e) {
            LogHelper::report($e, config('consts.message.error.E0000002'));

            return config('consts.message.error.E0000002');
        }

        return $pdfPath;
    }
}
