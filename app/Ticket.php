<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $dates = [
        'resolved_at'
    ];

    /**
     * @return BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(\App\TicketType::class);
    }

    /**
     * @return BelongsTo
     */
    public function caller()
    {
        return $this->belongsTo(\App\User::class);
    }

    /**
     * @return BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(\App\User::class);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeOpen($query)
    {
        return $query->where('state', '!=', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('state', '=', 'resolved');
    }
}
