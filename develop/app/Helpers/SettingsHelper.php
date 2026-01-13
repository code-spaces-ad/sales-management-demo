<?php

namespace App\Helpers;

use App\Models\System\Settings;

/**
 * 設定ヘルパークラス
 */
class SettingsHelper
{
    /**
     * 設定取得
     */
    public static function getSettings(): array
    {
        return Settings::query()->pluck('value', 'key')->toArray();
    }

    /**
     * 設定取得（キー指定）
     */
    public static function getValue($key): ?string
    {
        return self::getSettings()[$key] ?? null;
    }

    /**
     * 通知設定：メール送信の取得
     */
    public static function getSendMail(): bool
    {
        return (bool) self::getValue('send_mail');
    }

    /**
     * 通知設定：エラーメール送信の取得
     */
    public static function getSendErrorMail(): bool
    {
        return (bool) self::getValue('send_error_mail');
    }

    /**
     * 通知設定：エラーTeams送信
     */
    public static function getSendErrorTeams(): bool
    {
        return (bool) self::getValue('send_error_teams');
    }

    /**
     * 通知設定：エラーTeams通知用WebhookURL
     */
    public static function getErrorTeamsWebhookUrl(): string
    {
        return self::getValue('error_teams_webhook_url');
    }

    /**
     * 通知設定：ログインTeams送信
     */
    public static function getSendLoginTeams(): bool
    {
        return (bool) self::getValue('send_login_teams');
    }

    /**
     * 通知設定：ログインTeams通知用WebhookURL
     */
    public static function getLoginTeamsWebhookUrl(): string
    {
        return self::getValue('login_teams_webhook_url');
    }

    /**
     * 帳票設定：出力対象事業所
     */
    public static function getReportBankTransferFeeTargetOfficeFacilities(): array
    {
        return explode(',', self::getValue('bank_transfer_fee_target_office_facilities')) ?? [];
    }

    /**
     * 帳票設定：ソート順(事業所コード カンマ区切り)
     */
    public static function getReportBankTransferFeeSort(): array
    {
        return explode(',', self::getValue('bank_transfer_fee_sort')) ?? [];
    }

    /**
     * 帳票設定：事業所名除去文字列(カンマ区切り)
     */
    public static function getReportBankTransferFeeReplaceToBlank(): array
    {
        return explode(',', self::getValue('bank_transfer_fee_replace_blank')) ?? [];
    }
}
