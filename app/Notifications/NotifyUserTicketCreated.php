<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordDMMessage;
use App\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NotifyUserTicketCreated extends Notification
{
    use Queueable;

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
        $ticketUrl = route('help.tickets.show', $ticket);

        return (new DiscordDMMessage())
            ->to($ticket->caller->member->discord)
            ->message("Your ticket ({$ticketUrl}) has been created. Any future updates to your ticket will be sent here.")
            ->send();
    }
}
