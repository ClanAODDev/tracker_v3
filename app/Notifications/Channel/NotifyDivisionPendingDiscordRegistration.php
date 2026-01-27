<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\User;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionPendingDiscordRegistration extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private readonly User $user
    ) {}

    public function via(): array
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable)
    {
        return new BotChannelMessage($notifiable)
            ->title("{$notifiable->name} Division")
            ->target('officers')
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => '**NEW DISCORD REGISTRATION**',
                    'value' => sprintf(
                        ':wave: **%s** has registered via Discord and is interested in joining %s.',
                        $this->user->discord_username,
                        $notifiable->name
                    ),
                ],
            ])
            ->info()
            ->send();
    }
}
