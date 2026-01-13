<?php

/**
 * ユーザーマスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\System;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Helpers\UserHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\UserEditRequest;
use App\Http\Requests\Master\UserSearchRequest;
use App\Models\Master\MasterUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ユーザーマスター画面用コントローラー
 */
class MasterUserController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterUserController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param UserSearchRequest $request
     * @return View|RedirectResponse
     */
    public function index(UserSearchRequest $request): View|RedirectResponse
    {
        // 従業員の場合は、一覧画面を表示させず、編集画面に遷移する
        if (UserHelper::isRoleEmployee()) {
            return redirect()->route('system.users.edit', Auth::user()->id);
        }

        $search_condition_input_data = $request->validated();
        Session::put($this->refURLSystemKey(), URL::full());

        $search_condition_input_data['auth_role_id'] = Auth::user()->role_id;

        $data = [
            /** 検索項目 */
            'search_items' => [
                'roles' => [],
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'users' => MasterUser::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('system.users.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param UserSearchRequest $request
     * @return StreamedResponse
     */
    public function downloadExcel(UserSearchRequest $request): StreamedResponse
    {
        $search_condition_input_data = $request->validated();
        $search_condition_input_data['auth_role_id'] = Auth::user()->role_id;

        $filename = config('consts.excel.filename.users');
        $headings = [
            'コード',
            'ログインID',
            '名前',
            '備考',
            '作成日時',
            '更新日時',
        ];

        $users = MasterUser::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($users) {
                return $users->code_zerofill;
            },
            /** ログインID */
            function ($users) {
                return $users->login_id;
            },
            /** 名前 */
            function ($users) {
                return $users->name;
            },
            /** 備考 */
            function ($users) {
                return $users->note;
            },
            /** 作成日時 */
            function ($users) {
                return Carbon::parse($users->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($users) {
                return Carbon::parse($users->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        return $users->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterUser();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*system/users*', $this->refURLSystemKey());

        return view('system.users.create_edit', $this->sendDataUser($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(UserEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $user = new MasterUser();

        try {
            $user->code = $request->code;
            $user->login_id = $request->login_id;
            $user->password = Hash::make($request->password);
            $user->email = $request->email;
            $user->name = $request->name;
            $user->employee_id = $request->employee_id;
            $user->note = $request->note;

            // 新規登録は権限「従業員」固定
            $user->role_id = $request->role_id;

            $user->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $user->code_zero_fill, $user->name);

        // 一覧画面へリダイレクト
        return redirect(route('system.users.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterUser $user
     * @return View
     */
    public function edit(MasterUser $user): View
    {
        return view('system.users.create_edit', $this->sendDataUser($user));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserEditRequest $request
     * @param MasterUser $user
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(UserEditRequest $request, MasterUser $user): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $user->code = $request->code;
            $user->login_id = $request->login_id;
            $user->email = $request->email;
            $user->name = $request->name;
            $user->employee_id = $request->employee_id;
            $user->role_id = $request->role_id;
            $user->note = $request->note;

            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $user->code_zero_fill, $user->name);

        // 一覧画面へリダイレクト
        return redirect(route('system.users.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterUser $user
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterUser $user): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $user->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $user->code_zero_fill, $user->name);

        return redirect(route('system.users.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
