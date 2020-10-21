<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    use HasFactory;

    public function ticket()
    {
        return $this->belongsTo('App\Models\Ticket');
    }

    public function member()
    {
        return $this->belongsTo('App\Member', 'clan_id');
    }
}
