<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyAdminTicketUpdated extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    private string $update;

    public function __construct($update)
    {
        $this->update = $update;
    }

    public function via($notifiable)
    {
        return [BotChannel::class];
    }

    public function toBot($ticket)
    {
        if (! $ticket->owner?->settings()?->get('ticket_notifications')) {
            return [];
        }

        return (new BotDMMessage)
            ->to($ticket->owner?->member?->discord)
            ->message('Your ticket (' . route('help.tickets.show', $ticket) . ") has been updated: {$this->update}")
            ->send();
    }
}
