<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SlackNotification extends Notification
{
    use Queueable;

    /**
     * 通知メッセージ
     *
     * @var string
     */
    protected $message;

    /**
     * 添付情報
     *
     * @var array
     */
    protected $attachment;

    /**
     * Create a new notification instance.
     *
     * @param string $message
     * @param array $attachment
     * @return void
     */
    public function __construct($message, $attachment = null)
    {
        $this->message = $message;
        $this->attachment = $attachment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable): SlackMessage
    {
        $message = (new SlackMessage())
            ->from(config('app.name'))
            ->content($this->message);

        if (!is_null($this->attachment) && is_array($this->attachment)) {
            $message->attachment(function ($attachment) {
                if (isset($this->attachment['fallback'])) {
                    $attachment->fallback($this->attachment['fallback']);
                }
                $title_url = '';
                if (isset($this->attachment['title_url'])) {
                    $title_url = $this->attachment['title_url'];
                }
                if (isset($this->attachment['title'])) {
                    $attachment->title($this->attachment['title'], $title_url);
                }
                if (isset($this->attachment['content'])) {
                    $attachment->content($this->attachment['content']);
                }
                if (isset($this->attachment['fields']) && is_array($this->attachment['fields'])) {
                    foreach ($this->attachment['fields'] as $k => $v) {
                        $attachment->field($k, $v);
                    }
                }
                if (isset($this->attachment['footer'])) {
                    $attachment->footer($this->attachment['footer']);
                }
                if (isset($this->attachment['footer_icon'])) {
                    $attachment->footerIcon($this->attachment['footer_icon']);
                }
                if (isset($this->attachment['color'])) {
                    $attachment->color($this->attachment['color']);
                }
            });
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
