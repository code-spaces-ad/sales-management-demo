<?php

/**
 * 部門マスター画面用コントローラー
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
use App\Http\Requests\Master\DepartmentEditRequest;
use App\Http\Requests\Master\DepartmentSearchRequest;
use App\Models\Master\MasterDepartment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 部門マスター画面用コントローラー
 */
class MasterDepartmentController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterDepartmentController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param DepartmentSearchRequest $request
     * @return View
     */
    public function index(DepartmentSearchRequest $request): View
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
                'departments' => MasterDepartment::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.departments.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param DepartmentSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(DepartmentSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.departments');
        $headings = [
            'コード',
            '部門名',
            '部門カナ',
            '略称',
            '責任者',
            '備考',
            '作成日時',
            '更新日時',
        ];

        $departments = MasterDepartment::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($departments) {
                return $departments->code_zerofill;
            },
            /** 部門名 */
            function ($departments) {
                return $departments->name;
            },
            /** 部門カナ */
            function ($departments) {
                return $departments->name_kana;
            },
            /** 略称 */
            function ($departments) {
                return $departments->mnemonic_name;
            },
            /** 責任者 */
            function ($departments) {
                return $departments->mEmployee->name ?? '';
            },
            /** 備考 */
            function ($departments) {
                return $departments->note;
            },
            /** 作成日時 */
            function ($departments) {
                return Carbon::parse($departments->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($departments) {
                return Carbon::parse($departments->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        if ($departments->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.departments.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $departments->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterDepartment();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/departments*', $this->refURLMasterKey());

        return view('master.departments.create_edit', $this->sendDataDepartment($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param DepartmentEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(DepartmentEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $department = new MasterDepartment();

        try {
            $department->code = $request->code;
            $department->name = $request->name;
            $department->name_kana = $request->name_kana;
            $department->manager_id = $request->manager_id;
            $department->mnemonic_name = $request->mnemonic_name;
            $department->note = $request->note;

            $department->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $department->code_zero_fill, $department->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.departments.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterDepartment $department
     * @return View
     */
    public function edit(MasterDepartment $department): View
    {
        return view('master.departments.create_edit', $this->sendDataDepartment($department));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DepartmentEditRequest $request
     * @param MasterDepartment $department
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(DepartmentEditRequest $request, MasterDepartment $department): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $department->code = $request->code;
            $department->name = $request->name;
            $department->name_kana = $request->name_kana;
            $department->manager_id = $request->manager_id;
            $department->mnemonic_name = $request->mnemonic_name;
            $department->note = $request->note;

            $department->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $department->code_zero_fill, $department->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterDepartment $department
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterDepartment $department): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($department->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $department->code_zero_fill, $department->name);

            return redirect(route('master.departments.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            $department->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $department->code_zero_fill, $department->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.departments.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
