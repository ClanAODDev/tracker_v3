<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionForumRemovalFailed extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private readonly string $memberName,
        private readonly int $clanId,
        private readonly ?string $removerName,
        private readonly string $reason,
    ) {}

    public function via($notifiable): array
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable): array
    {
        return new BotChannelMessage($notifiable)
            ->title($notifiable->name . ' Division')
            ->target('officers')
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name'  => 'Forum removal needs attention',
                    'value' => sprintf(
                        '%s [%d] was removed from the Tracker%s, but their forum account could not be updated, so they may still have AOD access on the forums. Please ask a forum administrator to finish the removal.',
                        $this->memberName,
                        $this->clanId,
                        $this->removerName ? " by {$this->removerName}" : '',
                    ),
                ],
                [
                    'name'  => 'Reason',
                    'value' => $this->reason,
                ],
            ])
            ->error()
            ->send();
    }
}
