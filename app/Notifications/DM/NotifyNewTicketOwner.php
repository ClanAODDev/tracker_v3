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
        private readonly User $owner,
        private readonly ?User $assignedBy
    ) {}

    public function toBot($ticket): array
    {
        $attribution = $this->assignedBy ? " by {$this->assignedBy->name}" : ' automatically';

        return new BotDMMessage()
            ->to($this->owner->member?->discord)
            ->message("You were assigned a {$ticket->type->name} ticket (" . route('help.tickets.show', $ticket) . "){$attribution}")
            ->send();
    }
}
