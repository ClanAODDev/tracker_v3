<?php

namespace App\Notifications\Channel;

use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\User;
use App\Notifications\BaseNotification;

class NotifyDivisionMemberRequestPutOnHold extends BaseNotification
{
    public function __construct(
        private readonly MemberRequest $request,
        private readonly User $holder,
        private readonly Member $member
    ) {}

    public function toBot($notifiable): array
    {
        return new BotChannelMessage($notifiable)
            ->title($notifiable->name . ' Division')
            ->target('officers')
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => sprintf(
                        'A member status request for %s was put on hold by %s.',
                        $this->member->name,
                        $this->holder->name,
                    ),
                    'value' => 'Reason: ' . $this->request->notes,
                ],
            ])
            ->warning()
            ->send();
    }
}
