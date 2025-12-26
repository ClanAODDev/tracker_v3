<?php

namespace App\Notifications\Channel;

use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Traits\DivisionSettableNotification;
use App\Traits\RetryableNotification;

class NotifyDivisionMemberRequestApproved extends BaseNotification
{
    use DivisionSettableNotification, RetryableNotification;

    private string $alertSetting = 'chat_alerts.member_approved';

    public function __construct(
        private readonly User $approver,
        private readonly Member $member
    ) {}

    public function toBot($notifiable): array
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail($notifiable->getLogoPath())
            ->message(addslashes("**MEMBER STATUS REQUEST** - :thumbsup: A member status request for `{$this->member->name}` was approved by {$this->approver->name}!"))
            ->success()
            ->send();
    }
}
