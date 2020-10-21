<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = [
        'ticket_type',
        'caller',
        'owner',
    ];

    protected $dates = [
        'resolved_at'
    ];

    /**
     * @return BelongsTo
     */
    public function ticket_type()
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    /**
     * @return BelongsTo
     */
    public function caller()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    /**
     * @return BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeNew($query)
    {
        return $query->where('state', 'new')
            ->whereNotIn('state', ['assigned', 'resolved']);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeAssigned($query)
    {
        return $query->where('state', 'assigned')
            ->whereNotIn('state', ['new', 'resolved']);
    }

    public function scopeResolved($query)
    {
        return $query->where('state', 'resolved')
            ->whereNotIn('state', ['new', 'assigned']);
    }
}
