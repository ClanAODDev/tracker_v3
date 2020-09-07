<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TicketType extends Model
{
    public $guarded = [];

    /**
     * @var mixed
     */
    private $title;

    public function ticket()
    {
        return $this->hasMany(\App\Ticket::class);
    }
}
