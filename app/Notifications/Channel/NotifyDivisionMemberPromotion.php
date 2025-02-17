<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Traits\RetryableNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionMemberPromotion extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private readonly string $member,
        private readonly string $rank,
        private readonly bool $fromSync = false
    ) {}

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
            ->target($notifiable->settings()->get('chat_alerts.member_promoted'))
            ->thumbnail($notifiable->getLogoPath())
            ->message(addslashes(
                $this->fromSync
                    ? ":tools: **PROMOTION**\n{$this->member} rank is now `{$this->rank}` [Forum Sync Change]"
                    : ":tools: **PROMOTION**\n{$this->member} has accepted a promotion to  `{$this->rank}`"
            ))
            ->success()
            ->send();
    }
}
