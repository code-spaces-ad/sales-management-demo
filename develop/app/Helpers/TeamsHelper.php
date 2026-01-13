<?php

/**
 * @copyright © 2025 レボルシオン株式会社
 */

namespace App\Helpers;

use App\Services\TeamsService;

/**
 * Teamsヘルパークラス
 */
class TeamsHelper
{
    /**
     * エラー通知送信
     *
     * @param bool $teams_enabled
     * @param array $error_info
     * @return void
     */
    public static function sendErrorTeams(bool $teams_enabled, array $error_info)
    {
        if (!$teams_enabled) {
            return;
        }

        $teams_url = SettingsHelper::getErrorTeamsWebhookUrl();
        if (empty($teams_url)) {
            return;
        }

        // Teams通知
        $teamsService = new TeamsService();
        $teamsService->setTeamsUrl($teams_url)
            ->setErrorInfo($error_info)
            ->sendError();
    }

    /**
     * ログイン情報の設定
     *
     * @param array $login_info
     * @return TeamsService
     */
    public function setLoginInfo(array $login_info): self
    {
        $this->login_info = $login_info;

        return $this;
    }

    /**
     * ログイン通知送信
     */
    public static function sendLoginTeams(bool $teams_enabled, array $login_info): void
    {
        if (!$teams_enabled) {
            return;
        }

        $teams_url = SettingsHelper::getLoginTeamsWebhookUrl();
        if (empty($teams_url)) {
            return;
        }

        // Teams通知
        $teamsService = new TeamsService();
        $teamsService->setTeamsUrl($teams_url)
            ->setLoginInfo($login_info)
            ->sendLoginInfo();
    }
}
