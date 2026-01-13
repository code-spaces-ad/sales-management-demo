<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\MicrosoftTeams\MicrosoftTeamsChannel;
use NotificationChannels\MicrosoftTeams\MicrosoftTeamsMessage;

class TeamsNotification extends Notification
{
    use Queueable;

    /**
     * 通知タイトル
     *
     * @var string
     */
    protected $title;

    /**
     * 通知メッセージ
     *
     * @var string
     */
    protected $message;

    /**
     * Teams WebHookURL
     *
     * @var string
     */
    protected $teams_url;

    /**
     * Create a new notification instance.
     *
     * @param string $title
     * @param string $message
     * @param string $teams_url
     * @return void
     */
    public function __construct($title, $message, $teams_url)
    {
        $this->title = $title;
        $this->message = $message;
        $this->teams_url = $teams_url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [MicrosoftTeamsChannel::class];
    }

    /**
     * Get the Teams representation of the notification.
     *
     * @param mixed $notifiable
     * @return MicrosoftTeamsMessage
     */
    public function toMicrosoftTeams($notifiable): MicrosoftTeamsMessage
    {
        return MicrosoftTeamsMessage::create()
            ->to($this->teams_url)
            ->type('error')
            ->title($this->title)
            ->content($this->message);
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
