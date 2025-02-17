<?php

namespace App\Notifications\Channel;

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

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function toBot($ticket)
    {
        if (! $ticket->caller->member->discord) {
            throw new \Exception(sprintf(
                'Ticket %d caller could not be notified because they do not have a valid discord tag.',
                $ticket->id
            ));
        }

        if (! $ticket->caller->settings()->get('ticket_notifications')) {
            return [];
        }

        $target = $ticket->caller->member->discord;

        return (new BotDMMessage)
            ->to($target)
            ->message('Your ticket (' . route('help.tickets.show', $ticket) . ") has been updated: {$this->update}")
            ->send();
    }
}
