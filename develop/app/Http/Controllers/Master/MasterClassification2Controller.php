<?php

/**
 * 分類2マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\Classification2EditRequest;
use App\Http\Requests\Master\Classification2SearchRequest;
use App\Models\Master\MasterClassification2;
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
 * 分類2マスター画面用コントローラー
 */
class MasterClassification2Controller extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterClassification2Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param Classification2SearchRequest $request
     * @return View
     */
    public function index(Classification2SearchRequest $request): View
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
                'classifications2' => MasterClassification2::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.classifications2.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param Classification2SearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(Classification2SearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.classifications2');
        $headings = [
            'コード',
            '分類2名',
            '作成日時',
            '更新日時',
        ];

        $classifications2 = MasterClassification2::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($classifications2) {
                return $classifications2->code_zerofill;
            },
            /** 分類2名 */
            function ($classifications2) {
                return $classifications2->name;
            },
            /** 作成日時 */
            function ($classifications2) {
                return Carbon::parse($classifications2->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($classifications2) {
                return Carbon::parse($classifications2->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        if ($classifications2->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.classifications2.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $classifications2->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterClassification2();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/classifications2*', $this->refURLMasterKey());

        return view('master.classifications2.create_edit', $this->sendDataClassification2($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Classification2EditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(Classification2EditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $classification2 = new MasterClassification2();

        try {
            $classification2->code = $request->code;
            $classification2->name = $request->name;

            $classification2->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $classification2->code_zero_fill, $classification2->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.classifications2.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterClassification2 $classification2
     * @return View
     */
    public function edit(MasterClassification2 $classification2): View
    {
        return view('master.classifications2.create_edit', $this->sendDataClassification2($classification2));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Classification2EditRequest $request
     * @param MasterClassification2 $classification2
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(Classification2EditRequest $request, MasterClassification2 $classification2): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $classification2->code = $request->code;
            $classification2->name = $request->name;

            $classification2->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $classification2->code_zero_fill, $classification2->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterClassification2 $classification2
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterClassification2 $classification2): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($classification2->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $classification2->code_zero_fill, $classification2->name);

            return redirect(route('master.classifications2.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            $classification2->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $classification2->code_zero_fill, $classification2->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.classifications2.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
