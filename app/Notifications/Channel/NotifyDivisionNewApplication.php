<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionNewApplication extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    private string $alertSetting = 'chat_alerts.member_applied';

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
        return new BotChannelMessage($notifiable)
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => $this->threadTitle,
                    'value' => sprintf(
                        'Applying via AOD Forums — [View application](%s)',
                        $this->threadLink
                    ),
                ],
            ])->info()
            ->send();
    }
}
