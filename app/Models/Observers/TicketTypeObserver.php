<?php

namespace App\Models\Observers;

use App\Models\TicketType;
use Illuminate\Support\Str;

class TicketTypeObserver
{
    public function saving(TicketType $ticketType)
    {
        $ticketType->slug = Str::slug($ticketType->name);
    }

    public function deleting(TicketType $ticketType)
    {
        $ticketType->ticket()->delete();
    }
}
