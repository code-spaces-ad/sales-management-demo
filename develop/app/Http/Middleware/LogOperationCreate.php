<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Middleware;

use App\Enums\UserRoleType;
use App\Helpers\NotifyHelper;
use App\Models\Master\MasterUser;
use App\Models\System\LogOperation;
use Carbon\Carbon;
use Closure;
use Route;

/**
 * 操作ログ用ミドルウェア
 */
class LogOperationCreate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $this->operationLog($request, $response->getStatusCode());

        return $response;
    }

    /**
     * 操作ログ登録
     *
     * @param $request
     * @param $status
     */
    public function operationLog($request, $status)
    {
        $route_name = Route::currentRouteName();
        $request_url = $request->path();
        $user = $request->user();
        $user_id = $user->id ?? MasterUser::getUnknownUser(); // ユーザーIDを取得し、IDがnullだったら"unknown"ユーザーを取得

        // 登録対象のルート名判定
        if (!$this->checkAcceptPattern($route_name, $request_url, $user_id)) {
            // 対象外パターンはログ書き出ししない
            return;
        }

        // 対象期間外のデータ取得（ID)
        $target_date = Carbon::today()->subDay(config('consts.log_operation.log_retention_days'));
        $update_id = LogOperation::getOutOfTermId($target_date);

        $request_message = $request->toArray();
        if (array_key_exists('password', $request_message)) {
            // パスワードは除去する
            unset($request_message['password']);
        }

        $data = [
            /** ユーザーID */
            'user_id' => $user_id,
            /** ルート名 */
            'route_name' => $route_name,
            /** 要求パス */
            'request_url' => $request_url,
            /** 要求メソッド */
            'request_method' => $request->method(),
            /** HTTPステータスコード */
            'status_code' => $status,
            /** 要求内容 */
            'request_message' => count($request_message) != 0 ? json_encode($request_message) : null,
            /** クライアントIPアドレス */
            'remote_addr' => $request->ip(),
            /** ブラウザ名 */
            'user_agent' => $request->userAgent(),
        ];

        if (is_null($update_id)) {
            // create
            LogOperation::create($data);
        } else {
            // update
            $data['created_at'] = now();
            LogOperation::where('id', $update_id)->update($data);
        }

        // login通知
        if ($request_url === 'login' && isset($user) && $user->role_id !== UserRoleType::SYS_ADMIN) {
            $login_info = NotifyHelper::makeLoginToArray(url()->current(), $request, $user);
            // ログイン通知
            NotifyHelper::loginNotify($login_info);
        }
    }

    /**
     * ログ書き出し　許可パターンチェック
     *
     * @param string $route_name ルート名
     * @param string $request_url 要求パス
     * @param string $user_id ユーザーID
     * @return bool|mixed
     */
    private function checkAcceptPattern($route_name, $request_url, $user_id)
    {
        // ログ対象ルート名取得
        $accept_route_name = config('consts.log_operation.accept_route_name');

        foreach ($accept_route_name as $key => $val) {
            if ($key === 'other') {
                continue;
            }

            // ルート名 or 要求パス：後方一致検索
            if (preg_match("/.*{$key}$/", $route_name) || preg_match("/.*{$key}$/", $request_url)) {
                if ($key === 'login') {
                    // login の場合は、user_id あり／なしで判定
                    return !is_null($user_id);
                }

                return $val;
            }
        }

        // その他の設定値を返す
        return $accept_route_name['other'];
    }
}
