<?php

/**
 * 請求書発行画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Invoice;

use App\Helpers\ClosingDateHelper;
use App\Helpers\LogHelper;
use App\Helpers\PdfHelper;
use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\InvoicePrintRequest;
use App\Http\Requests\Invoice\InvoicePrintSearchRequest;
use App\Models\Invoice\ChargeData;
use App\Models\Invoice\ChargeDetail;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterOfficeFacility;
use App\Services\Excel\SalesInvoiceBranchSummaryExcelService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * 請求書発行画面用コントローラー
 */
class InvoicePrintController extends Controller
{
    /**
     * InvoicePrintController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param InvoicePrintSearchRequest $request
     * @return View
     */
    public function index(InvoicePrintSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put('search_condition_input_data.invoice_print', $search_condition_input_data);
        Session::put('invoice_url', URL::full());

        // 締年月日の範囲年月取得
        [$charge_date_start, $charge_date_end] = ClosingDateHelper::getChargeCloseTermDate(
            $search_condition_input_data['charge_date'], $search_condition_input_data['closing_date']);

        $billing_exists_list = [
            1 => '有り',
        ];

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('code')->get(),
                /** 請求締日 */
                'closing_date_list' => ClosingDateHelper::getClosingDateList(),
                /** 請求有無 */
                'billing_exists_list' => $billing_exists_list,
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 事業所データ */
                'office_facilities' => MasterOfficeFacility::query()->get(),
                /** 担当者マスター */
                'employees' => MasterEmployee::query()->oldest('code')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                /** 締年月日データ */
                'charge_closing_date_display' => ClosingDateHelper::getChargeClosingDateDisplay(
                    $charge_date_end, $search_condition_input_data['closing_date']),
                'charge_date_start' => $charge_date_start,
                'charge_date_end' => $charge_date_end,
                /** 請求データ */
                'charge_data' => ChargeData::getSearchResult($search_condition_input_data),
            ],
        ];

        return view('invoice.invoice_print.index', $data);
    }

    /**
     * PDF表示（Excel -> PDF変換）
     *
     * @param InvoicePrintRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function showPdf(InvoicePrintRequest $request): RedirectResponse
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        //　請求データID
        $charge_data_ids = explode(',', $request->input('charge_data_ids'));

        // 出力先サブフォルダ作成（Excel/PDF）
        $date_name = date('YmdHis');
        $new_dir_path_excel = storage_path(config('consts.excel.temp_path')) . $date_name;
        $this->makeFolder($new_dir_path_excel);
        $new_dir_path_pdf = public_path(config('consts.pdf.temp_path')) . $date_name;
        $this->makeFolder($new_dir_path_pdf);

        // 出力先サブフォルダ作成（ZIP）
        $pdf_zip_dir = public_path(config('consts.pdf.temp_path')) . 'zip';
        if (!file_exists($pdf_zip_dir)) {
            $this->makeFolder($pdf_zip_dir);
        }

        // 請求データ毎に、Excel⇒PDF　ファイルを作成
        $arr_pdf_files[] = '';
        foreach ($charge_data_ids as $key => $charge_data_id) {
            $customer_id = ChargeData::find($charge_data_id)->customer_id;
            // 自分以外の得意先の請求先に設定されている場合は書式＝４（子会社別明細）
            $child_customer_ids = ChargeDetail::getOrderBillingCustomer($charge_data_id, $customer_id);
            if (count($child_customer_ids) > 0) {
                $invoice_format_type = 4;
            } else {
                $invoice_format_type = MasterCustomer::find($customer_id)->sales_invoice_format_type;
            }
            LogHelper::info(__CLASS__, '請求書タイプ:', $invoice_format_type);

            $excel_service = new SalesInvoiceBranchSummaryExcelService();
            // テンプレートファイルパス設定
            $temp_path2 = config('consts.excel.template_file.sale_invoice_print');

            $temp_path1 = config('consts.excel.template_path');
            $path = storage_path($temp_path1 . $temp_path2);

            $reader = new XReader();
            $spreadsheet_template = $reader->load($path);

            // 出力ファイル作成
            $spreadsheet = $excel_service->getSpreadSheet($spreadsheet_template, $request,
                '2', $charge_data_id);

            $file_name_pre = date('YmdHis');

            $customer_name = 'InvoiceNo';
            $excel_file_name = "{$file_name_pre}_$customer_name$charge_data_id.xlsx";
            $pdf_file_name = "{$file_name_pre}_$customer_name$charge_data_id.pdf";

            $arr_pdf_files[] = $pdf_file_name;

            // 一旦、Excelファイルを保存
            $excel_path = $new_dir_path_excel . '/' . $excel_file_name;
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save($excel_path);

            // Excel -> PDF 変換
            PdfHelper::convertPdf($excel_path, $new_dir_path_pdf);

            // メモリ解放
            $spreadsheet_template->disconnectWorksheets();
            $spreadsheet_template->garbageCollect();
            unset($spreadsheet_template);
            $spreadsheet->disconnectWorksheets();
            $spreadsheet->garbageCollect();
            unset($spreadsheet);
        }

        $merge_file_name = public_path(config('consts.pdf.temp_path')) . '請求書' . $date_name . '.pdf';
        $redirect_file_name = config('consts.pdf.temp_path') . '請求書' . $date_name . '.pdf';
        PdfHelper::joinPdf($new_dir_path_pdf, $arr_pdf_files, $merge_file_name);

        return redirect(asset('/') . $redirect_file_name);
    }

    /**
     * @param string $path
     * @return void
     */
    private function makeFolder(string $path): void
    {
        mkdir($path);
        chmod($path, 0777);
    }

    /**
     * CONVMV コマンド実行
     *
     * @param string $pdf_file
     */
    private function convmv(string $pdf_file): void
    {
        exec("convmv -r -f utf8 -t sjis '$pdf_file' --notest ");
    }

    /**
     * ZIP コマンド実行
     *
     * @param string $path
     * @param string $zip_file
     */
    private function zip(string $path, string $zip_file): void
    {
        chdir($path);
        exec("zip -r $zip_file .");
    }

    /**
     * デフォルトセット
     *
     * @return array
     */
    private function defaultSet(): array
    {
        $search_condition_input_data = [];

        // 得意先
        $search_condition_input_data['customer_id'] = null;
        // 請求期間
        $search_condition_input_data['charge_date'] = Carbon::now()->format('Y-m');
        // 締日区分
        $search_condition_input_data['closing_date'] = 0;

        $billing_exists[0] = 1;
        $search_condition_input_data['billing_exists'] = $billing_exists;

        return $search_condition_input_data;
    }
}
