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
    public function __construct(private readonly string $threadTitle, private readonly string $threadLink) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable)
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => $this->threadTitle,
                    'value' => $this->threadLink,
                ],
            ])->info()
            ->send();
    }
}
