<?php

namespace App;

use App\Activities\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use RecordsActivity;

    protected $guarded = [];

    protected $dates = [
        'resolved_at'
    ];

    /**
     * @return BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('App\TicketType');
    }

    /**
     * @return BelongsTo
     */
    public function caller()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return BelongsTo
     */
    public function division()
    {
        return $this->belongsTo('App\Division');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeOpen($query)
    {
        return $query->where('state', '!=', 'resolved');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeClosed($query)
    {
        return $query->where('state', '=', 'resolved');
    }

    /**
     * @param User $user
     */
    public function assignTo(User $user)
    {
        $this->owner()->associate($user->id);
        $this->save();
    }

    public function ownTicketToMe()
    {
        $this->owner()->associate(auth()->id());
        $this->save();
    }
}
