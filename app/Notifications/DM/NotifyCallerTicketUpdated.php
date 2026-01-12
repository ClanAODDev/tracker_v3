<?php

namespace App\Notifications\DM;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Models\Ticket;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyCallerTicketUpdated extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private Ticket $ticket,
        private string $update
    ) {}

    public function via()
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable)
    {
        if (! $this->ticket->caller?->settings()?->get('ticket_notifications')) {
            return [];
        }

        $ticketUrl = route('help.tickets.show', $this->ticket);

        return new BotDMMessage()
            ->to($this->ticket->caller?->member?->discord)
            ->message("Your ticket ({$ticketUrl}) has been updated: {$this->update}")
            ->send();
    }
}
