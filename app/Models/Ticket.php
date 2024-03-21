<?php

namespace App\Models;

use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

/**
 * @property string state
 * @property mixed resolved_at
 */
class Ticket extends Model
{
    use HasFactory;
    use Notifiable;

    public $stateColors = [
        'new' => 'info',
        'assigned' => 'accent',
        'resolved' => 'success',
        'rejected' => 'danger',
    ];

    protected $guarded = [];

    protected $with = [
        'type',
        'caller',
        'owner',
    ];

    protected $dates = [
        'resolved_at',
    ];

    /**
     * @return BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
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
        return $this->hasMany(TicketComment::class)
            ->orderBy('created_at', 'DESC');
    }

    /**
     * @return BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return mixed|Repository
     */
    public function routeNotificationForWebhook()
    {
        return config('app.aod.discord_webhook');
    }

    /**
     * @return mixed
     */
    public function scopeNew($query)
    {
        return $query->where('state', 'new')
            ->whereNotIn('state', ['assigned', 'resolved']);
    }

    /**
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

    public function getStateColorAttribute()
    {
        return $this->stateColors[$this->state];
    }

    public function ownTo(\Illuminate\Contracts\Auth\Authenticatable $user)
    {
        $this->owner()->associate($user);
        $this->state = 'assigned';
        $this->save();

        if ($user === auth()->user()) {
            $this->say('owned the ticket');
        } else {
            $this->say(auth()->user()->name . ' assigned the ticket to ' . $user->name);
        }
    }

    public function isResolved()
    {
        return $this->resolved_at;
    }

    public function resolve()
    {
        $this->state = 'resolved';
        $this->owner_id = auth()->id();
        $this->resolved_at = now();
        $this->save();
        $this->say('resolved the ticket');
    }

    public function reopen()
    {
        $this->state = 'assigned';
        $this->resolved_at = null;
        $this->save();
        $this->say('reopened the ticket');
    }

    public function reject()
    {
        $this->state = 'rejected';
        $this->resolved_at = now();
        $this->owner_id = auth()->id();
        $this->save();
        $this->say('rejected the ticket');
    }

    public function say(string $comment)
    {
        $this->comments()->create([
            'user_id' => auth()->id(),
            'body' => $comment,
        ]);
    }
}
