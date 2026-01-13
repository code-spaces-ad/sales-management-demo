<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $title;

    protected $error_info;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $error_info)
    {
        // タイトル設定
        $this->title = '【' . env('APP_COMPANY_NAME') . '】エラー通知';
        // エラー情報
        $this->error_info = $error_info;
    }

    /**
     * Build the message.
     *
     * @return NotificationEmail
     */
    public function build(): NotificationEmail
    {
        return $this
            ->subject($this->title)
            ->view('emails.notification_email')
            ->with(
                [
                    'notify_date' => Carbon::now()->format('Y/m/d H:i:s'),
                    'error_info' => $this->error_info,
                ]
            );

    }
}
