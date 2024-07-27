<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyAdminTicketUpdated extends Notification implements ShouldQueue
{
    use Queueable;

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
        if (! $ticket->owner) {
            // ticket hasn't been assigned, so we have no one to notify
            return [];
        }
        if (! $ticket->owner->member->discord) {
            throw new \Exception(auth()->user()->name . ' could not be notified because they do not have a valid discord.');
        }

        if (! $ticket->owner->settings()->get('ticket_notifications')) {
            return [];
        }

        $target = $ticket->owner->member->discord;

        return (new BotDMMessage)
            ->to($target)
            ->message('Your ticket (' . route('help.tickets.show', $ticket) . ") has been updated: {$this->update}")
            ->send();
    }
}
