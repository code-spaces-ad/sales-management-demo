<?php

/**
 * 種別マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\KindEditRequest;
use App\Http\Requests\Master\KindSearchRequest;
use App\Models\Master\MasterKind;
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
 * 種別マスター画面用コントローラー
 */
class MasterKindController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterKindController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param KindSearchRequest $request
     * @return View
     */
    public function index(KindSearchRequest $request): View
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
                'kinds' => MasterKind::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.kinds.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param KindSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(KindSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.kinds');
        $headings = [
            'コード',
            '種別名',
            '作成日時',
            '更新日時',
        ];

        $kinds = MasterKind::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($kinds) {
                return $kinds->code_zerofill;
            },
            /** 種別名 */
            function ($kinds) {
                return $kinds->name;
            },
            /** 作成日時 */
            function ($kinds) {
                return Carbon::parse($kinds->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($kinds) {
                return Carbon::parse($kinds->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        if ($kinds->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.kinds.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $kinds->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterKind();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/kinds*', $this->refURLMasterKey());

        return view('master.kinds.create_edit', $this->sendDataKind($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param KindEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(KindEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $kind = new MasterKind();

        try {
            $kind->code = $request->code;
            $kind->name = $request->name;

            $kind->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $kind->code_zero_fill, $kind->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.kinds.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterKind $kind
     * @return View
     */
    public function edit(MasterKind $kind): View
    {
        return view('master.kinds.create_edit', $this->sendDataKind($kind));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param KindEditRequest $request
     * @param MasterKind $kind
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(KindEditRequest $request, MasterKind $kind): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $kind->code = $request->code;
            $kind->name = $request->name;

            $kind->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $kind->code_zero_fill, $kind->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterKind $kind
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterKind $kind): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($kind->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $kind->code_zero_fill, $kind->name);

            return redirect(route('master.kinds.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            $kind->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $kind->code_zero_fill, $kind->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.kinds.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
