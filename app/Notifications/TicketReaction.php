<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotReactMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TicketReaction extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private string $status)
    {
        //
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

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function toBot($notifiable)
    {
        return (new BotReactMessage($notifiable))
            ->status($this->status)
            ->send();
    }
}
