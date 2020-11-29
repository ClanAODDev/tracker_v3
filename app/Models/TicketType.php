<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    public $guarded = [];

    /**
     * @var mixed
     */
    private $title;

    public function ticket()
    {
        return $this->hasMany(Ticket::class);
    }
}
