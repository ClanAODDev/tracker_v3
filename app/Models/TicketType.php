<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    public $guarded = [];

    protected $casts = [
        'role_access' => 'json',
    ];

    public function ticket()
    {
        return $this->hasMany(Ticket::class);
    }

    public function auto_assign_to()
    {
        return $this->belongsTo(User::class);
    }
}
