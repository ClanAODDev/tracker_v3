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

    const RECRUITING_CHANNEL = 'https://discord.com/channels/507758143774916609/508656360028766219';

    public function __construct(
        private readonly User $user,
        private readonly bool $hasApplication = false,
    ) {}

    public function via(): array
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable)
    {
        $message = $this->hasApplication
            ? ':clipboard: **%s** has submitted an application to join %s. Check %s'
            : ':wave: **%s** has registered via Discord and is interested in joining %s. Check %s';

        $heading = $this->hasApplication
            ? '**APPLICATION SUBMITTED**'
            : '**NEW DISCORD REGISTRATION**';

        return new BotChannelMessage($notifiable)
            ->title("{$notifiable->name} Division")
            ->target('officers')
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => $heading,
                    'value' => sprintf(
                        $message,
                        $this->user->discord_username,
                        $notifiable->name,
                        self::RECRUITING_CHANNEL
                    ),
                ],
            ])
            ->info()
            ->send();
    }
}
