<?php

/**
 * 事業所マスター画面用コントローラー
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
use App\Http\Requests\Master\OfficeFacilityEditRequest;
use App\Http\Requests\Master\OfficeFacilitySearchRequest;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 事業所マスター画面用コントローラー
 */
class MasterOfficeFacilityController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterOfficeFacilityController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param OfficeFacilitySearchRequest $request
     * @return View
     */
    public function index(OfficeFacilitySearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLMasterKey(), URL::full());

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'office_facilities' => MasterOfficeFacility::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.office_facilities.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param OfficeFacilitySearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(OfficeFacilitySearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.office_facilities');
        $headings = [
            'コード',
            '部門名',
            '事業所名',
            '担当者',
            '備考',
            '作成日時',
            '更新日時',
        ];

        $office_facilities = MasterOfficeFacility::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($office_facilities) {
                return $office_facilities->code_zerofill;
            },
            /** 部門名 */
            function ($office_facilities) {
                return $office_facilities->mDepartment->name ?? '';
            },
            /** 事業所名 */
            function ($office_facilities) {
                return $office_facilities->name;
            },
            /** 担当者 */
            function ($office_facilities) {
                return $office_facilities->mEmployee->name ?? '';
            },
            /** 備考 */
            function ($office_facilities) {
                return $office_facilities->note;
            },
            /** 作成日時 */
            function ($office_facilities) {
                return Carbon::parse($office_facilities->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($office_facilities) {
                return Carbon::parse($office_facilities->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        if ($office_facilities->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.office_facilities.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $office_facilities->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterOfficeFacility();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/office_facilities*', $this->refURLMasterKey());

        return view('master.office_facilities.create_edit', $this->sendDataOfficeFacility($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OfficeFacilityEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(OfficeFacilityEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $office_facility = new MasterOfficeFacility();

        try {
            $office_facility->code = $request->code;
            $office_facility->name = $request->name;
            $office_facility->department_id = $request->department_id;
            $office_facility->manager_id = $request->manager_id;
            $office_facility->note = $request->note;

            $office_facility->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $office_facility->code_zero_fill, $office_facility->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.office_facilities.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterOfficeFacility $office_facility
     * @return View
     */
    public function edit(MasterOfficeFacility $office_facility): View
    {
        return view('master.office_facilities.create_edit', $this->sendDataOfficeFacility($office_facility));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OfficeFacilityEditRequest $request
     * @param MasterOfficeFacility $office_facility
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(OfficeFacilityEditRequest $request, MasterOfficeFacility $office_facility): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $office_facility->code = $request->code;
            $office_facility->name = $request->name;
            $office_facility->department_id = $request->department_id;
            $office_facility->manager_id = $request->manager_id;
            $office_facility->note = $request->note;

            $office_facility->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $office_facility->code_zero_fill, $office_facility->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterOfficeFacility $office_facility
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterOfficeFacility $office_facility): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($office_facility->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $office_facility->code_zero_fill, $office_facility->name);

            return redirect(route('master.office_facilities.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            $office_facility->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $office_facility->code_zero_fill, $office_facility->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.office_facilities.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
