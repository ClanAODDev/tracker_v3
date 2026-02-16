<?php

namespace App\Notifications\Channel;

use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Traits\DivisionSettableNotification;
use App\Traits\HasRecruitmentFields;
use App\Traits\RetryableNotification;

class NotifyDivisionNewMemberRecruited extends BaseNotification
{
    use DivisionSettableNotification, HasRecruitmentFields, RetryableNotification;

    private string $alertSetting = 'chat_alerts.member_created';

    public function __construct(
        private readonly Member $member,
        private readonly User $recruiter
    ) {}

    public function toBot($notifiable): array
    {
        return new BotChannelMessage($notifiable)
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name'  => ':crossed_swords: New Member Recruited',
                    'value' => sprintf(
                        '%s just recruited [%s](%s) into the %s Division!',
                        $this->recruiter->name,
                        $this->member->name,
                        route('member', $this->member->getUrlParams()),
                        $notifiable->name
                    ),
                ],
                $this->buildAssignmentField($this->member),
                $this->buildHandleField($this->member),
            ])
            ->success()
            ->send();
    }
}
