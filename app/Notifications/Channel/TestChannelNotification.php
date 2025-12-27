<?php

namespace App\Notifications\Channel;

use App\Channels\Messages\BotChannelMessage;
use App\Notifications\BaseNotification;

class TestChannelNotification extends BaseNotification
{
    public function __construct(
        private readonly string $target
    ) {}

    public function toBot($notifiable): array
    {
        return (new BotChannelMessage($notifiable))
            ->title('Channel Test')
            ->target($this->target)
            ->thumbnail($notifiable->getLogoPath())
            ->message('This is a test notification to verify channel configuration.')
            ->info()
            ->send();
    }
}
