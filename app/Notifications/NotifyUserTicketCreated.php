<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyUserTicketCreated extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

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
    public function toBot($ticket)
    {
        $ticketUrl = route('help.tickets.show', $ticket);

        return (new BotDMMessage)
            ->to($ticket->caller->member->discord)
            ->message("Your ticket ({$ticketUrl}) has been created. Any future updates to your ticket will be sent here.")
            ->send();
    }
}
