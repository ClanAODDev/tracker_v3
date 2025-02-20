<?php

namespace App\Notifications\DM;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyMemberAwardReceived extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private readonly string $award
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
                'Congratulations - you received an award! %s. This award has been added to [your profile](%s)',
                $this->award,
                route('member', $notifiable->getUrlParams())
            ))
            ->send();
    }
}
