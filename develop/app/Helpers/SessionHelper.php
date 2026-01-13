<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * セッション ヘルパークラス
 */
class SessionHelper
{
    /**
     * セッションを削除
     *
     * @param string $url_string URL文字列
     * @param string|null $session_key セッションキー
     * @param string $another_url URLを2つ比較したい場合の文字列
     * @return void
     */
    public static function forgetSessionForMismatchURL(string $url_string, ?string $session_key, string $another_url = ''): void
    {
        // 前回のURLと一致しなかったら、セッションを削除
        if (Str::is($url_string, url()->previous()) || Str::is($another_url, url()->previous())) {
            return;
        }
        Session::forget($session_key);
    }
}
