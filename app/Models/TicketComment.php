<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Touches;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Touches(['ticket'])]
#[Unguarded]
class TicketComment extends Model
{
    use HasFactory;

    protected $with = [
        'user',
    ];

    public function ticket()
    {
        return $this->belongsTo('App\Models\Ticket');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
