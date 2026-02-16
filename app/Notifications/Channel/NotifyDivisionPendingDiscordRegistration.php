<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\DivisionApplication;
use App\Models\User;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionPendingDiscordRegistration extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    const RECRUITING_CHANNEL = 'https://discord.com/channels/507758143774916609/508656360028766219';

    const INTRODUCTIONS_CHANNEL = 'https://discord.com/channels/507758143774916609/1137896866286157834';

    public function __construct(
        private readonly User $user,
        private readonly ?DivisionApplication $application = null,
    ) {}

    public function via(): array
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable)
    {
        if ($this->application) {
            $applicationUrl = url("divisions/{$notifiable->slug}?application={$this->application->id}");
            $message        = sprintf(
                ':clipboard: **%s** has submitted an application to join %s. [View application](%s)',
                $this->user->name,
                $notifiable->name,
                $applicationUrl
            );
            $heading = '**APPLICATION SUBMITTED**';
        } else {
            $message = sprintf(
                ':wave: **%s** has registered via Discord and is interested in joining %s. Check %s, %s',
                $this->user->name,
                $notifiable->name,
                self::RECRUITING_CHANNEL,
                self::INTRODUCTIONS_CHANNEL
            );
            $heading = '**NEW DISCORD REGISTRATION**';
        }

        return new BotChannelMessage($notifiable)
            ->title("{$notifiable->name} Division")
            ->target('officers')
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name'  => $heading,
                    'value' => $message,
                ],
            ])
            ->info()
            ->send();
    }
}
