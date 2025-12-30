<?php

namespace App\Notifications\DM;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyCallerTicketUpdated extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    private string $update;

    /**
     * Create a new notification instance.
     */
    public function __construct($update)
    {
        $this->update = $update;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via()
    {
        return [BotChannel::class];
    }

    public function toBot($ticket)
    {
        if (! $ticket->caller?->settings()?->get('ticket_notifications')) {
            return [];
        }

        return (new BotDMMessage)
            ->to($ticket->caller?->member?->discord)
            ->message('Your ticket (' . route('help.tickets.show', $ticket) . ") has been updated: {$this->update}")
            ->send();
    }
}
