<?php

namespace App\Notifications\Channel;

use App\Channels\Messages\BotChannelMessage;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Traits\DivisionSettableNotification;
use App\Traits\RetryableNotification;

class NotifyDivisionSettingsEdited extends BaseNotification
{
    use DivisionSettableNotification, RetryableNotification;

    private string $alertSetting = 'chat_alerts.division_edited';

    public function __construct(
        private readonly User $user
    ) {}

    public function toBot($notifiable): array
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail($notifiable->getLogoPath())
            ->message(sprintf('%s updated the division settings', $this->user->name))
            ->info()
            ->send();
    }
}
