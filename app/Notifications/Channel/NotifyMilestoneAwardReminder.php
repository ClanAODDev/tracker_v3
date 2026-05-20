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

class NotifyMilestoneAwardReminder extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private readonly Collection $members,
        private readonly string $monthLabel,
    ) {}

    public function via($notifiable): array
    {
        return [BotChannel::class];
    }

    /**
     * @throws Exception
     */
    public function toBot($notifiable): array
    {
        $memberList = $this->members
            ->map(fn ($m) => "• **{$m->name}** — {$m->years_since_joined} Years of Service")
            ->join("\n");

        return new BotChannelMessage($notifiable)
            ->title($notifiable->name . ' Division')
            ->target('officers')
            ->thumbnail($notifiable->getLogoPath())
            ->message(
                ":trophy: **Tenure Milestone Reminder — {$this->monthLabel}**\n\n"
                . "The following members have reached a milestone anniversary this month "
                . "but have not yet received their tenure award:\n\n"
                . $memberList
            )
            ->warning()
            ->send();
    }
}
