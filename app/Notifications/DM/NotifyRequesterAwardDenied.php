<?php

namespace App\Notifications\DM;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyRequesterAwardDenied extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private readonly string $award,
        private readonly string $member,
        private readonly string $denialReason,
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
                'Unfortunately the award [%s] you requested for %s was denied - . The reason for the denial was: %s',
                $this->award,
                $this->member,
                $this->denialReason
            ))
            ->send();
    }
}
