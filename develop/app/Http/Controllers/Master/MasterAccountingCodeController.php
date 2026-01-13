<?php

/**
 * 経理コードマスター画面用コントローラー
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
use App\Http\Requests\Master\AccountingCodeEditRequest;
use App\Http\Requests\Master\AccountingCodeSearchRequest;
use App\Models\Master\MasterAccountingCode;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 経理コードマスター画面用コントローラー
 */
class MasterAccountingCodeController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterAccountingCodeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param AccountingCodeSearchRequest $request
     * @return View
     */
    public function index(AccountingCodeSearchRequest $request): View
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
                'accounting_codes' => MasterAccountingCode::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.accounting_codes.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param AccountingCodeSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(AccountingCodeSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.accounting_codes');
        $headings = [
            'コード',
            '経理コード名',
            '備考',
            '出力対象',
            '作成日時',
            '更新日時',
        ];

        $accounting_codes = MasterAccountingCode::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($accounting_codes) {
                return $accounting_codes->code_zerofill;
            },
            /** 経理コード名 */
            function ($accounting_codes) {
                return $accounting_codes->name;
            },
            /** 備考 */
            function ($accounting_codes) {
                return $accounting_codes->note;
            },
            /** 出力対象 */
            function ($accounting_codes) {
                return (string) $accounting_codes->output_group;
            },
            /** 作成日時 */
            function ($accounting_codes) {
                return Carbon::parse($accounting_codes->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($accounting_codes) {
                return Carbon::parse($accounting_codes->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        if ($accounting_codes->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.accounting_codes.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $accounting_codes->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterAccountingCode();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/accounting_codes*', $this->refURLMasterKey());

        return view('master.accounting_codes.create_edit', $this->sendDataAccountingCode($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AccountingCodeEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(AccountingCodeEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $accounting_code = new MasterAccountingCode();

        try {
            $accounting_code->code = $request->code;
            $accounting_code->name = $request->name;
            $accounting_code->note = $request->note;
            $accounting_code->output_group = $request->output_group;

            $accounting_code->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $accounting_code->code_zero_fill, $accounting_code->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.accounting_codes.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterAccountingCode $accounting_code
     * @return View
     */
    public function edit(MasterAccountingCode $accounting_code): View
    {
        return view('master.accounting_codes.create_edit', $this->sendDataAccountingCode($accounting_code));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AccountingCodeEditRequest $request
     * @param MasterAccountingCode $accounting_code
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(AccountingCodeEditRequest $request, MasterAccountingCode $accounting_code): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $accounting_code->code = $request->code;
            $accounting_code->name = $request->name;
            $accounting_code->note = $request->note;
            $accounting_code->output_group = $request->output_group;

            $accounting_code->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $accounting_code->code_zero_fill, $accounting_code->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterAccountingCode $accounting_code
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterAccountingCode $accounting_code): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($accounting_code->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $accounting_code->code_zero_fill, $accounting_code->name);

            return redirect(route('master.accounting_codes.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            $accounting_code->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $accounting_code->code_zero_fill, $accounting_code->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.accounting_codes.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
