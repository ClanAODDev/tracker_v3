<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Traits\RetryableNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class NotifyDivisionLoaExpired extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    /**
     * @param  Collection<int, array{name: string, reason: string, end_date: string}>  $leaves
     */
    public function __construct(private readonly Collection $leaves) {}

    public function via($notifiable): array
    {
        return [BotChannel::class];
    }

    /**
     * @throws Exception
     */
    public function toBot($notifiable): array
    {
        $list = $this->leaves
            ->map(fn (array $l) => "• **{$l['name']}** — {$l['reason']}, expired {$l['end_date']}")
            ->join("\n");

        return new BotChannelMessage($notifiable)
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get('chat_alerts.loa_expired'))
            ->thumbnail($notifiable->getLogoPath())
            ->message(
                ":calendar: **Leave of Absence Expired**\n\n"
                . "The following member(s)' leave of absence has expired. Confirm they've returned or follow up:\n\n"
                . $list
            )
            ->warning()
            ->send();
    }
}
