<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberAwarded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $member, private string $award) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [BotChannel::class];
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get('chat_alerts.member_awarded'))
            ->thumbnail($notifiable->getLogoPath())
            ->message(sprintf('%s received an award: %s', $this->member, $this->award))
            ->info()
            ->send();
    }
}
