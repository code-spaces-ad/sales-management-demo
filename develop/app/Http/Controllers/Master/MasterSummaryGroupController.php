<?php

/**
 * 集計グループマスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\SummaryGroupEditRequest;
use App\Http\Requests\Master\SummaryGroupSearchRequest;
use App\Models\Master\MasterSummaryGroup;
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
 * 集計グループマスター画面用コントローラー
 */
class MasterSummaryGroupController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterSummaryGroupController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param SummaryGroupSearchRequest $request
     * @return View
     */
    public function index(SummaryGroupSearchRequest $request): View
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
                'summary_group' => MasterSummaryGroup::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.summary_group.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param SummaryGroupSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(SummaryGroupSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.summary_group');
        $headings = [
            'コード',
            '集計グループ名',
            '備考',
            '作成日時',
            '更新日時',
        ];

        $summary_groups = MasterSummaryGroup::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($summary_groups) {
                return $summary_groups->code_zerofill;
            },
            /** 集計グループ名 */
            function ($summary_groups) {
                return $summary_groups->name;
            },
            /** 備考 */
            function ($summary_groups) {
                return $summary_groups->note;
            },
            /** 作成日時 */
            function ($summary_groups) {
                return Carbon::parse($summary_groups->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($summary_groups) {
                return Carbon::parse($summary_groups->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        if ($summary_groups->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.summary_group.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $summary_groups->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterSummaryGroup();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/summary_group*', $this->refURLMasterKey());

        return view('master.summary_group.create_edit', $this->sendDataSummaryGroup($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SummaryGroupEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(SummaryGroupEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $summary_group = new MasterSummaryGroup();

        try {
            $summary_group->code = $request->code;
            $summary_group->name = $request->name;
            $summary_group->note = $request->note;

            $summary_group->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $summary_group->code_zero_fill, $summary_group->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.summary_group.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterSummaryGroup $summary_group
     * @return View
     */
    public function edit(MasterSummaryGroup $summary_group): View
    {
        return view('master.summary_group.create_edit', $this->sendDataSummaryGroup($summary_group));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SummaryGroupEditRequest $request
     * @param MasterSummaryGroup $summary_group
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(SummaryGroupEditRequest $request, MasterSummaryGroup $summary_group): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $summary_group->code = $request->code;
            $summary_group->name = $request->name;
            $summary_group->note = $request->note;

            $summary_group->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $summary_group->code_zero_fill, $summary_group->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterSummaryGroup $summary_group
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterSummaryGroup $summary_group): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($summary_group->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $summary_group->code_zero_fill, $summary_group->name);

            return redirect(route('master.summary_group.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            $summary_group->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $summary_group->code_zero_fill, $summary_group->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.summary_group.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
