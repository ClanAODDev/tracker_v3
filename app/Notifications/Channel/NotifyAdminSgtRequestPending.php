<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyAdminSgtRequestPending extends Notification implements ShouldQueue
{
    use Queueable;
    use RetryableNotification;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly string $requester,
        private readonly string $member,
        private readonly string $rank,
        private readonly int $rankActionId,
    ) {}

    public function via()
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable)
    {
        return (new BotChannelMessage($notifiable))
            ->title('SGT+ Request')
            ->target('admin')
            ->message(sprintf(
                '%s submitted a `%s` request for %s. [View Rank Action](https://tracker.clanaod.net/operations/rank-actions/%d/edit)',
                $this->requester,
                $this->rank,
                $this->member,
                $this->rankActionId
            ))
            ->info()
            ->send();
    }
}
