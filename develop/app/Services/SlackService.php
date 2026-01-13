<?php

namespace App\Services;

use App\Notifications\SlackNotification;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;

class SlackService
{
    use Notifiable;

    /**
     * 通知用WebhookURL
     *
     * @var string
     */
    protected $slack_url = '';

    /**
     * エラー情報
     *
     * @var array
     */
    protected $error_info;

    /**
     * 通知用WebhookURLの設定
     *
     * @param string $slack_url
     * @return SlackService
     */
    public function setSlackUrl(string $slack_url): self
    {
        $this->slack_url = $slack_url;

        return $this;
    }

    /**
     * エラー情報の設定
     *
     * @param array $error_info
     * @return SlackService
     */
    public function setErrorInfo(array $error_info): self
    {
        $this->error_info = $error_info;

        return $this;
    }

    /**
     * Slack通知処理
     *
     * @return void
     */
    public function send()
    {
        // Slack通知
        if ($this->slack_url) {
            // 本文利用（アタッチメント未使用）
            $this->sendToSlack($this->makeSlackMessage());
        }
    }

    /**
     * メッセージをSlackで通知する。
     *
     * @param string $message
     * @param array|null $attachment
     * @return void
     */
    public function sendToSlack(string $message, ?array $attachment = null)
    {
        $this->notify(new SlackNotification($message, $attachment));
    }

    /**
     * @return string
     */
    protected function routeNotificationForSlack(): string
    {
        return $this->slack_url;
    }

    /**
     * Slack送信内容作成
     *
     * @return string
     */
    public function makeSlackMessage(): string
    {
        $message = '';

        // タイトル
        $message .= $this->mdBold('【' . env('APP_COMPANY_NAME') . '】エラー通知)') . "\r\n";
        // 発生日時
        $message .= '発生日時：' . Carbon::now()->format('Y/m/d H:i:s') . "\r\n\r\n";

        // 固定文言
        $message .= "エラーが発生しています。\r\n以下をご確認ください。\r\n\r\n";

        // セパレータ
        $message .= $this->getSeparate() . "\r\n";

        // message
        $message .= '[Message] ' . $this->error_info['message'] . "\r\n\r\n";
        // status
        $message .= '[Status] ' . $this->error_info['status'] . "\r\n";
        // file
        $message .= '[File] ' . $this->error_info['file'] . "\r\n";
        // line
        $message .= '[Line] ' . $this->error_info['line'] . "\r\n";
        // url
        $message .= '[URL] ' . $this->error_info['url'] . "\r\n";

        return $message;
    }

    /**
     * Slack送信内容作成(アタッチメント)
     *
     * @return array
     */
    public function makeSlackAttachment(): array
    {
        $attachment = [];

        return $attachment;
    }

    /**
     * Markdown太字
     *
     * @param string $str
     * @return string
     */
    private function mdBold(string $str): string
    {
        return '*' . $str . '*';
    }

    /**
     * Markdownリンク
     *
     * @param string $str
     * @param string $target_link
     * @return string
     */
    private function mdLink(string $str, string $target_link): string
    {
        return '<' . $target_link . '|' . $str . '>';
    }

    /**
     * @return string
     */
    private function getSeparate(): string
    {
        return '----------------------------------------------------------------------------------------------';
    }
}
