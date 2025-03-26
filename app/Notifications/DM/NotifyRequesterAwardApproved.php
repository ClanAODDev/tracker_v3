<?php

namespace App\Notifications\DM;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Notifications\Channel\NotifyDivisionFailedAwardApprovalNotice;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyRequesterAwardApproved extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private readonly string $award,
        private readonly string $member,
    ) {
    }

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

    public function failed(): void
    {
        $this->action->member->division->notify(
            new NotifyDivisionFailedAwardApprovalNotice(
                $this->action->member,
                $this->member,
                $this->award
            )
        );
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
                'An award you requested for %s was approved!. They have been notified and their profile was updated with the award: %s',
                $this->member,
                $this->award,
            ))
            ->send();
    }
}
