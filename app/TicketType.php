<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    public function ticket()
    {
        return $this->hasMany(\App\Ticket::class);
    }
}
