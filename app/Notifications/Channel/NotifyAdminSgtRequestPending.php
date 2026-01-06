<?php

namespace App\Notifications\Channel;

use App\Channels\Messages\BotChannelMessage;
use App\Notifications\BaseNotification;
use App\Traits\RetryableNotification;

class NotifyAdminSgtRequestPending extends BaseNotification
{
    use RetryableNotification;

    public function __construct(
        private readonly string $requester,
        private readonly string $member,
        private readonly string $rank,
        private readonly int $rankActionId,
    ) {}

    public function toBot($notifiable): array
    {
        $url = route('filament.admin.resources.rank-actions.edit', $this->rankActionId);

        return new BotChannelMessage($notifiable)
            ->title('SGT+ Request')
            ->target('admin')
            ->message(sprintf(
                '%s submitted a `%s` request for %s. [View Rank Action](%s)',
                $this->requester,
                $this->rank,
                $this->member,
                $url
            ))
            ->warning()
            ->send();
    }
}
