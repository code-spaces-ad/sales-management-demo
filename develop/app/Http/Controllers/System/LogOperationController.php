<?php

/**
 * 操作履歴画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\System;

use App\Helpers\SearchConditionSetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\System\LogOperationSearchRequest;
use App\Models\System\LogOperation;
use Carbon\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 操作履歴画面用コントローラー
 */
class LogOperationController extends Controller
{
    /**
     * LogOperationController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param LogOperationSearchRequest $request
     * @return View
     */
    public function index(LogOperationSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());

        $data = [
            /** 検索項目 */
            'search_items' => [],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'log_operations' => LogOperation::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('system.log_operations.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param LogOperationSearchRequest $request
     * @return StreamedResponse
     */
    public function downloadExcel(LogOperationSearchRequest $request): StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.log_operations');
        $headings = [
            '操作日時', 'ログインID', 'ユーザ名', 'ルート名', '要求パス', '要求メソッド',
            'HTTPステータスコード', '要求内容', 'クライアントIPアドレス', 'ブラウザ名',
            '作成日時',
        ];

        $log_operations = LogOperation::getSearchResult($search_condition_input_data);

        $filters = [
            /** 操作日時 */
            function ($log_operations) {
                return Carbon::parse($log_operations->created_at)->format('Y/m/d H:i:s');
            },
            /** ログインID */
            function ($log_operations) {
                return $log_operations->mUser->login_id;
            },
            /** ユーザー名 */
            function ($log_operations) {
                return $log_operations->mUser->name;
            },
            /** ルート名 */
            function ($log_operations) {
                return $log_operations->route_name;
            },
            /** 要求パス */
            function ($log_operations) {
                return $log_operations->request_url;
            },
            /** 要求メソッド */
            function ($log_operations) {
                return $log_operations->request_method;
            },
            /** HTTPステータスコード */
            function ($log_operations) {
                return $log_operations->status_code;
            },
            /** 要求内容 */
            function ($log_operations) {
                return $log_operations->request_message;
            },
            /** クライアントIPアドレス */
            function ($log_operations) {
                return $log_operations->remote_addr;
            },
            /** ブラウザ名 */
            function ($log_operations) {
                return $log_operations->user_agent;
            },
        ];

        return $log_operations->exportExcel($filename, $headings, $filters);
    }
}
