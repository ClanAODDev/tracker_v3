<?php

namespace App\Notifications\DM;

use App\Channels\Messages\BotDMMessage;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Traits\RetryableNotification;

class NotifyNewTicketOwner extends BaseNotification
{
    use RetryableNotification;

    public function __construct(
        private readonly User $assignedOwner,
        private readonly User $oldUser
    ) {}

    public function toBot($ticket): array
    {
        return (new BotDMMessage)
            ->to($this->assignedOwner->member->discord)
            ->message('You were assigned to a ticket (' . route('help.tickets.show', $ticket) . ") by {$this->oldUser->name}")
            ->send();
    }
}
