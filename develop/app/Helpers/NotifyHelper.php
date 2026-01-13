<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Models\Master\MasterUser;
use Exception;
use Illuminate\Http\Request;

/**
 * 通知ヘルパークラス
 */
class NotifyHelper
{
    /**
     * エラーメール送信
     *
     * @param array $error_info
     * @return void
     */
    public static function errorNotify(array $error_info): void
    {
        // メール通知
        MailHelper::sendErrorMail(SettingsHelper::getSendErrorMail(), $error_info);
        // Teams通知
        TeamsHelper::sendErrorTeams(SettingsHelper::getSendErrorTeams(), $error_info);
    }

    /**
     * ログイン通知送信
     */
    public static function loginNotify(array $login_info): void
    {
        // Teams通知
        TeamsHelper::sendLoginTeams(SettingsHelper::getSendLoginTeams(), $login_info);
    }

    /**
     * エラー情報構築
     *
     * @param string $url
     * @param int $status
     * @param Exception $e
     * @return array
     */
    public static function makeErrorToArray(string $url, int $status, Exception $e): array
    {
        $error['message'] = $e->getMessage();
        $error['status'] = $status;
        $error['code'] = $e->getCode();
        $error['file'] = $e->getFile();
        $error['line'] = $e->getLine();
        $error['url'] = $url;

        return $error;
    }

    /**
     * ログイン情報構築
     *
     * @param string $url
     * @param Request $request
     * @param MasterUser $user
     * @return array
     */
    public static function makeLoginToArray(string $url, Request $request, MasterUser $user): array
    {
        $login_info['login_id'] = $user->id;
        $login_info['login_name'] = $user->name;
        $login_info['role_id'] = $user->role_id;
        $login_info['role_name'] = $user->role_name;
        $login_info['ipaddress'] = $request->ip();
        $login_info['user_agent'] = $request->userAgent();
        $login_info['url'] = $url;

        return $login_info;
    }
}
