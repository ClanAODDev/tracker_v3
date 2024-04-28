<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessageReact;
use App\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TicketReaction extends Notification implements ShouldQueue
{
    use Queueable;

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
        return [WebhookChannel::class];
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function toWebhook($ticket)
    {
        return (new DiscordMessageReact())
            ->to(channel: config('app.aod.admin-ticketing-channel'))
            ->messageId($ticket->message_id)
            ->status($this->status)
            ->send();
    }
}
