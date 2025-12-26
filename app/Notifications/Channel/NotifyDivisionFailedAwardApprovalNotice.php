<?php

namespace App\Notifications\Channel;

use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use App\Notifications\BaseNotification;

class NotifyDivisionFailedAwardApprovalNotice extends BaseNotification
{
    public function __construct(
        private readonly Member $requester,
        private readonly string $member,
        private readonly string $awardName
    ) {}

    public function toBot($notifiable): array
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->target('officers')
            ->fields([
                [
                    'name' => sprintf(
                        '%s requested an award for %s that was approved [%s], but their Discord privacy settings prevent receiving it. Please work with the user to update their settings.',
                        $this->requester->name,
                        $this->member,
                        $this->awardName,
                    ),
                    'value' => sprintf(
                        'Discord - %s (%s)',
                        $this->requester->discord,
                        $this->requester->discord_id,
                    ),
                ],
            ])
            ->error()
            ->send();
    }
}
