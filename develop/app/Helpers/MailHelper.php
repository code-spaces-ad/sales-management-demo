<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Mail\NotificationEmail;

/**
 * メールヘルパークラス
 */
class MailHelper
{
    /**
     * エラーメール送信
     *
     * @param bool $email_enabled
     * @param array $error_info
     * @return void
     */
    public static function sendErrorMail(bool $email_enabled, array $error_info)
    {
        if (!$email_enabled) {
            return;
        }

        $email = env('NOTIFICATIONS_MAIL_ADDRESS');

        // メール通知
        if ($email) {
            \Mail::to($email)->send(
                new NotificationEmail($error_info)
            );
        }
    }
}
