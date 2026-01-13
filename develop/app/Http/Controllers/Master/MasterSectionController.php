<?php

/**
 * 管理部署マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\SectionEditRequest;
use App\Http\Requests\Master\SectionSearchRequest;
use App\Models\Master\MasterSection;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 管理部署マスター画面用コントローラー
 */
class MasterSectionController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterSectionController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param SectionSearchRequest $request
     * @return View
     */
    public function index(SectionSearchRequest $request): View
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
                'sections' => MasterSection::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.sections.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param SectionSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(SectionSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.sections');
        $headings = [
            'コード',
            '管理部署名',
            '作成日時',
            '更新日時',
        ];

        $sections = MasterSection::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($sections) {
                return $sections->code_zerofill;
            },
            /** 管理部署名 */
            function ($sections) {
                return $sections->name;
            },
            /** 作成日時 */
            function ($sections) {
                return Carbon::parse($sections->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($sections) {
                return Carbon::parse($sections->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        if ($sections->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.sections.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $sections->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterSection();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/sections*', $this->refURLMasterKey());

        return view('master.sections.create_edit', $this->sendDataSection($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SectionEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(SectionEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $section = new MasterSection();

        try {
            $section->code = $request->code;
            $section->name = $request->name;

            $section->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $section->code_zero_fill, $section->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.sections.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterSection $section
     * @return View
     */
    public function edit(MasterSection $section): View
    {
        return view('master.sections.create_edit', $this->sendDataSection($section));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SectionEditRequest $request
     * @param MasterSection $section
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(SectionEditRequest $request, MasterSection $section): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $section->code = $request->code;
            $section->name = $request->name;

            $section->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $section->code_zero_fill, $section->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterSection $section
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterSection $section): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($section->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $section->code_zero_fill, $section->name);

            return redirect(route('master.sections.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            $section->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $section->code_zero_fill, $section->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.sections.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
