<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Filament\Admin\Resources\TicketResource;
use App\Models\Ticket;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyAdminTicketUpdated extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(
        private Ticket $ticket,
        private string $update
    ) {}

    public function via($notifiable)
    {
        return [BotChannel::class];
    }

    public function toBot($notifiable)
    {
        if (! $this->ticket->owner?->settings()?->get('ticket_notifications')) {
            return [];
        }

        $ticketUrl = TicketResource::getUrl('edit', ['record' => $this->ticket]);

        return new BotDMMessage()
            ->to($this->ticket->owner?->member?->discord)
            ->message("Ticket #{$this->ticket->id} has a new comment: {$this->update}\n{$ticketUrl}")
            ->send();
    }
}
