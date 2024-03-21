<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordDMMessage;
use App\Channels\WebhookChannel;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NotifyNewTicketOwner extends Notification
{
    use Queueable;

    private User $assignedOwner;

    private User $oldUser;

    public function __construct(User $assignedOwner, User $oldUser)
    {
        $this->assignedOwner = $assignedOwner;
        $this->oldUser = $oldUser;
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
        $target = $this->assignedOwner->member->discord;

        return (new DiscordDMMessage())
            ->to($target)
            ->message('You were assigned to a ticket (' . route('help.tickets.show', $ticket) . ") by {$this->oldUser->name}")
            ->send();
    }
}
