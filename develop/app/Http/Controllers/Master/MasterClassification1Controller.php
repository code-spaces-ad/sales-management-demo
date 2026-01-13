<?php

/**
 * 分類1マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\Classification1EditRequest;
use App\Http\Requests\Master\Classification1SearchRequest;
use App\Models\Master\MasterClassification1;
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
 * 分類1マスター画面用コントローラー
 */
class MasterClassification1Controller extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterClassification1Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param Classification1SearchRequest $request
     * @return View
     */
    public function index(Classification1SearchRequest $request): View
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
                'classifications1' => MasterClassification1::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.classifications1.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param Classification1SearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(Classification1SearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.classifications1');
        $headings = [
            'コード',
            '分類1名',
            '作成日時',
            '更新日時',
        ];

        $classifications1 = MasterClassification1::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($classifications1) {
                return $classifications1->code_zerofill;
            },
            /** 分類1名 */
            function ($classifications1) {
                return $classifications1->name;
            },
            /** 作成日時 */
            function ($classifications1) {
                return Carbon::parse($classifications1->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($classifications1) {
                return Carbon::parse($classifications1->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        if ($classifications1->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.classifications1.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $classifications1->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterClassification1();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/classifications1*', $this->refURLMasterKey());

        return view('master.classifications1.create_edit', $this->sendDataClassification1($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Classification1EditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(Classification1EditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $classification1 = new MasterClassification1();

        try {
            $classification1->code = $request->code;
            $classification1->name = $request->name;

            $classification1->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $classification1->code_zero_fill, $classification1->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.classifications1.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterClassification1 $classification1
     * @return View
     */
    public function edit(MasterClassification1 $classification1): View
    {
        return view('master.classifications1.create_edit', $this->sendDataClassification1($classification1));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Classification1EditRequest $request
     * @param MasterClassification1 $classification1
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(Classification1EditRequest $request, MasterClassification1 $classification1): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $classification1->code = $request->code;
            $classification1->name = $request->name;

            $classification1->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $classification1->code_zero_fill, $classification1->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterClassification1 $classification1
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterClassification1 $classification1): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($classification1->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $classification1->code_zero_fill, $classification1->name);

            return redirect(route('master.classifications1.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            $classification1->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $classification1->code_zero_fill, $classification1->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.classifications1.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
