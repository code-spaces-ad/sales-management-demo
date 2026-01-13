<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Services\SlackService;

/**
 * Slackヘルパークラス
 */
class SlackHelper
{
    /**
     * エラーメール送信
     *
     * @param bool $slack_enabled
     * @param array $error_info
     * @return void
     */
    public static function sendErrorSlack(bool $slack_enabled, array $error_info)
    {
        if (!$slack_enabled) {
            return;
        }

        $slack_url = config('consts.default.common.slack_webhook_url', '');
        if (empty($slack_url)) {
            return;
        }

        // Slack通知
        $slackService = new SlackService();
        $slackService->setSlackUrl($slack_url)
            ->setErrorInfo($error_info)
            ->send();
    }
}
