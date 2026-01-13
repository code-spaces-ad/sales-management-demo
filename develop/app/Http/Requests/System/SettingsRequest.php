<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 設定画面用 リクエストクラス
 */
class SettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'notification.send_mail' => ['bail', 'required', 'bool'],
            'notification.send_error_mail' => ['bail', 'required', 'bool'],
            'notification.send_error_teams' => ['bail', 'required', 'bool'],
            'notification.error_teams_webhook_url' => ['bail', 'nullable', 'string'],
            'notification.send_login_teams' => ['bail', 'required', 'bool'],
            'notification.login_teams_webhook_url' => ['bail', 'nullable', 'string'],

            'report.bank_transfer_fee_target_office_facilities' => ['bail', 'nullable', 'array'],
            'report.bank_transfer_fee_sort' => ['bail', 'nullable', 'string'],
            'report.bank_transfer_fee_replace_blank' => ['bail', 'nullable', 'string'],
        ];
    }

    /**
     * 項目名
     */
    public function attributes(): array
    {
        return [
            'notification.send_mail' => 'メール送信',
            'notification.send_error_mail' => 'エラーメール送信',
            'notification.send_error_teams' => 'エラーTeams送信',
            'notification.error_teams_webhook_url' => 'エラーTeams通知用WebhookURL',
            'notification.send_login_teams' => 'ログインTeams送信',
            'notification.login_teams_webhook_url' => 'ログインTeams通知用WebhookURL',

            'report.bank_transfer_fee_target_office_facilities' => '振込手数料 - 出力対象事業所',
            'report.bank_transfer_fee_sort' => '振込手数料 - ソート順',
            'report.bank_transfer_fee_replace_blank' => '振込手数料 - 事業所名除去文字列',
        ];
    }
}
