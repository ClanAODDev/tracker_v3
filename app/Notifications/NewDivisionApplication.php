<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewDivisionApplication extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly string $title, private readonly string $link) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [BotChannel::class];
    }

    public function toBot(object $notifiable)
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => $this->title,
                    'value' => $this->link,
                ],
            ])->info()
            ->send();
    }
}
