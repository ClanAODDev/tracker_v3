<?php

namespace App\Notifications\DM;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyRemoverForumRemovalFailed extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private readonly string $memberName,
        private readonly int $clanId,
        private readonly string $reason,
    ) {}

    public function via($notifiable): array
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable): array
    {
        return new BotDMMessage()
            ->to($notifiable->discord_id)
            ->message(sprintf(
                "**Forum removal needs attention**\r\n%s (%d) was removed from the Tracker, but their forum account could not be updated, so they may still have AOD access on the forums. Please ask a forum administrator to finish the removal.\r\n\r\nReason: %s",
                $this->memberName,
                $this->clanId,
                $this->reason
            ))
            ->send();
    }
}
