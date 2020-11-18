<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NotifyAdminTicketCreated extends Notification
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
     * @param $ticket
     * @return array
     * @throws \Exception
     */
    public function toWebhook($ticket)
    {
        $channel = config('app.aod.admin-ticketing-channel');

        $authoringUser = auth()->check() ? auth()->user()->name : 'UNK';

        return (new DiscordMessage())
            ->to($channel)
            ->info()
            ->fields([
                [
                    'name' => "Type: {$ticket->type->name}",
                    'value' => "Submitted by {$authoringUser}"
                ], [
                    'name' => 'Link to ticket',
                    'value' => route('help.tickets.show', $ticket)
                ]
            ])->send();
    }
}
