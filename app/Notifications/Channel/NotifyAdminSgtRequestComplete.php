<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NotifyAdminSgtRequestComplete extends Notification
{
    use Queueable;
    use RetryableNotification;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly string $member,
        private readonly string $rank,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via()
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable)
    {
        return new BotChannelMessage($notifiable)
            ->title('SGT+ Request')
            ->target('admin')
            ->message(sprintf(
                "%s's promotion to `%s` was accepted, but additional permissions may be needed.",
                $this->member,
                $this->rank,
            ))
            ->success()
            ->send();
    }
}
