<?php

namespace App\Services;

use App\Models\Master\MasterHeadOfficeInfo;
use App\Notifications\TeamsNotification;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;

class TeamsService
{
    use Notifiable;

    /**
     * 通知用WebhookURL
     *
     * @var string
     */
    protected $teams_url = '';

    /**
     * エラー情報
     *
     * @var array
     */
    protected $error_info;

    /**
     * 通知用WebhookURLの設定
     *
     * @param string $teams_url
     * @return TeamsService
     */
    public function setTeamsUrl(string $teams_url): self
    {
        $this->teams_url = $teams_url;

        return $this;
    }

    /**
     * エラー情報の設定
     *
     * @param array $error_info
     * @return TeamsService
     */
    public function setErrorInfo(array $error_info): self
    {
        $this->error_info = $error_info;

        return $this;
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
     * Teams通知処理
     *
     * @return void
     */
    public function sendError()
    {
        // Teams通知
        if ($this->teams_url) {
            // 本文利用（アタッチメント未使用）
            $this->sendToTeams($this->makeTeamsErrorTitle(), $this->makeTeamsErrorMessage(), $this->teams_url);
        }
    }

    /**
     * Teams ログイン通知処理
     *
     * @return void
     */
    public function sendLoginInfo()
    {
        // Teams通知
        if ($this->teams_url) {
            // 本文利用（アタッチメント未使用）
            $this->sendToTeams($this->makeTeamsLoginTitle(), $this->makeTeamsLoginMessage(), $this->teams_url);
        }
    }

    /**
     * メッセージをTeamsで通知する。
     *
     * @param string $title
     * @param string $message
     * @param string $url
     * @return void
     */
    public function sendToTeams(string $title, string $message, string $url)
    {
        $this->notify(new TeamsNotification($title, $message, $url));
    }

    /**
     * @return string
     */
    protected function routeNotificationForTeams(): string
    {
        return $this->teams_url;
    }

    /**
     * Teams送信タイトル作成
     *
     * @return string
     */
    public function makeTeamsErrorTitle(): string
    {
        $title = '';
        // タイトル
        $title .= '【' . env('APP_COMPANY_NAME') . '】エラー通知';

        return $title;
    }

    /**
     * Teams送信 ログイン通知 タイトル作成
     *
     * @return string
     */
    public function makeTeamsLoginTitle(): string
    {
        $title = '';
        // タイトル
        $title .= '【' . MasterHeadOfficeInfo::getCompanyName() . '】ログイン通知';

        return $title;
    }

    /**
     * Teams送信 取込スキップ タイトル作成
     *
     * @return string
     */
    public function makeTeamsSkipTitle(): string
    {
        $title = '';
        // タイトル
        $title .= '【' . env('APP_COMPANY_NAME') . '】取込スキップ通知';

        return $title;
    }

    /**
     * Teams送信 エラー通知内容作成
     *
     * @return string
     */
    public function makeTeamsErrorMessage(): string
    {
        $message = '';

        // 発生日時
        $message .= '発生日時：' . Carbon::now()->format('Y/m/d H:i:s') . '<br><br>';

        // 固定文言
        $message .= 'エラーが発生しています。<br>以下をご確認ください。<br><br>';

        // セパレータ
        $message .= $this->getSeparate() . '<br>';

        // message
        $message .= '[Message] ' . str_replace("\n", '<br>', $this->error_info['message']) . '<br><br>';
        // status
        $message .= '[Status] ' . $this->error_info['status'] . '<br>';
        // file
        $message .= '[File] ' . $this->error_info['file'] . '<br>';
        // line
        $message .= '[Line] ' . $this->error_info['line'] . '<br>';
        // url
        $message .= "[URL] <a href='" . $this->error_info['url'] . "' target='_blank'>" . $this->error_info['url'] . '</a><br>';

        return $message;
    }

    /**
     * Teams送信 ログイン通知内容作成
     *
     * @return string
     */
    public function makeTeamsLoginMessage(): string
    {
        $message = '';

        // ログイン日時
        $message .= '発生日時：' . Carbon::now()->format('Y/m/d H:i:s') . '<br><br>';

        // 固定文言
        $message .= 'ログインを検知しました。<br>以下をご確認ください。<br><br>';

        // セパレータ
        $message .= $this->getSeparate() . '<br>';

        // ログインID
        $message .= '[ログインID] ' . $this->login_info['login_id'] . '<br>';
        // ログインユーザー
        $message .= '[ログインユーザー] ' . $this->login_info['login_name'] . '<br>';
        // 権限
        $message .= '[権限] ' . $this->login_info['role_name'] . '<br>';
        // IPアドレス
        $message .= '[IPアドレス] ' . $this->login_info['ipaddress'] . '<br>';
        // ブラウザ
        $message .= '[ブラウザ] ' . $this->login_info['user_agent'] . '<br>';
        // url
        $message .= "[URL] <a href='" . $this->login_info['url'] . "' target='_blank'>" . $this->login_info['url'] . '</a><br>';

        return $message;
    }

    /**
     * @return string
     */
    private function getSeparate(): string
    {
        return '----------------------------------------------------------------------------------------------';
    }
}
