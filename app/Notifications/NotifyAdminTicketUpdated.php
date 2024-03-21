<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordDMMessage;
use App\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NotifyAdminTicketUpdated extends Notification
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
        return [WebhookChannel::class];
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function toWebhook($ticket)
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

        return (new DiscordDMMessage())
            ->to($target)
            ->message('Your ticket (' . route('help.tickets.show', $ticket) . ") has been updated: {$this->update}")
            ->send();
    }
}
