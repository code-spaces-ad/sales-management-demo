<?php

/**
 * 社員マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\EmployeeEditRequest;
use App\Http\Requests\Master\EmployeeSearchRequest;
use App\Models\Master\MasterEmployee;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 社員マスター画面用コントローラー
 */
class MasterEmployeeController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterEmployeeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param EmployeeSearchRequest $request
     * @return View
     */
    public function index(EmployeeSearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLMasterKey(), URL::full());

        $data = [
            /** 検索項目 */
            'search_items' => [],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'employees' => MasterEmployee::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.employees.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param EmployeeSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(EmployeeSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.employees');
        $headings = [
            'コード', '社員名', '社員名かな', '生年月日', '入社日', '備考',
            '作成日時', '更新日時',
        ];
        $employees = MasterEmployee::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($employees) {
                return $employees->code_zerofill;
            },
            /** 社員名 */
            function ($employees) {
                return $employees->name;
            },
            /** 社員名かな */
            function ($employees) {
                return $employees->name_kana;
            },
            /** 生年月日 */
            function ($employees) {
                return $employees->birthday_ymd;
            },
            /** 入社日 */
            function ($employees) {
                return $employees->hire_date_ymd;
            },
            /** 備考 */
            function ($employees) {
                return $employees->note;
            },
            /** 作成日時 */
            function ($employees) {
                return $employees->created_at;
            },
            /** 更新日時 */
            function ($employees) {
                return $employees->updated_at;
            },
        ];

        if ($employees->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.employees.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $employees->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterEmployee();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/employees*', $this->refURLMasterKey());

        return view('master.employees.create_edit', $this->sendDataEmployee($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EmployeeEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(EmployeeEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $employee = new MasterEmployee();

        try {
            $employee->code = $request->code;
            $employee->name = $request->name;
            $employee->name_kana = $request->name_kana;
            $employee->department_id = $request->department_id;
            $employee->office_facilities_id = $request->office_facilities_id;
            $employee->birthday = $request->birthday;
            $employee->hire_date = $request->hire_date;
            $employee->note = $request->note;

            $employee->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $employee->code_zero_fill, $employee->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.employees.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterEmployee $employee
     * @return View
     */
    public function edit(MasterEmployee $employee): View
    {
        return view('master.employees.create_edit', $this->sendDataEmployee($employee));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param EmployeeEditRequest $request
     * @param MasterEmployee $employee
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(EmployeeEditRequest $request, MasterEmployee $employee): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $employee->code = $request->code;
            $employee->name = $request->name;
            $employee->name_kana = $request->name_kana;
            $employee->department_id = $request->department_id;
            $employee->office_facilities_id = $request->office_facilities_id;
            $employee->birthday = $request->birthday;
            $employee->hire_date = $request->hire_date;
            $employee->note = $request->note;

            $employee->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $employee->code_zero_fill, $employee->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterEmployee $employee
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterEmployee $employee): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($employee->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $employee->code_zero_fill, $employee->name);

            return redirect(route('master.employees.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            $employee->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $employee->code_zero_fill, $employee->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.employees.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
