<?php

namespace App\Models\Observers;

use App\Models\TicketType;
use Illuminate\Support\Str;

class TicketTypeObserver
{
    /**
     * Handle the ticket type "created" event.
     */
    public function saving(TicketType $ticketType)
    {
        $ticketType->slug = Str::slug($ticketType->name);
    }
}
