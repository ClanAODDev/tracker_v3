<?php

namespace App\Notifications\DM;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Models\RankAction;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyRequesterOfficerRankActionApproved extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private readonly RankAction $action,
    ) {}

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
     * @throws \Exception
     */
    public function toBot($notifiable)
    {
        return (new BotDMMessage)
            ->to($notifiable->discord_id)
            ->message(sprintf(
                '*Action required*: An officer rank action you requested [%s to %s] was approved. Please coordinate with the member to award the promotion.',
                $this->action->member->name,
                $this->action->rank->getLabel(),
            ))
            ->send();
    }
}
