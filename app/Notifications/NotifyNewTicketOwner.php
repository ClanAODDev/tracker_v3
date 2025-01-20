<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotDMMessage;
use App\Models\User;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyNewTicketOwner extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

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
        return [BotChannel::class];
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function toBot($ticket)
    {
        $target = $this->assignedOwner->member->discord;

        return (new BotDMMessage)
            ->to($target)
            ->message('You were assigned to a ticket (' . route('help.tickets.show', $ticket) . ") by {$this->oldUser->name}")
            ->send();
    }
}
