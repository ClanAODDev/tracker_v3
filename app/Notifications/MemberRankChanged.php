<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Traits\RetryableNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberRankChanged extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(private readonly string $member, private readonly string $rank) {}

    public function via($notifiable)
    {
        return [BotChannel::class];
    }

    /**
     * @param  mixed  $notifiable
     * @return array
     *
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get('chat_alerts.rank_changed'))
            ->thumbnail($notifiable->getLogoPath())
            ->message(addslashes(":tools: **MEMBER STATUS - RANK CHANGE**\n`{$this->member}` is now  `{$this->rank}`."))
            ->success()
            ->send();
    }
}
