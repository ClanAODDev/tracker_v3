<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Ticket extends Model
{
    use HasFactory;
    use Notifiable;

    public $stateColors = [
        'new'      => 'info',
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

    protected $casts = [
        'resolved_at' => 'datetime',
        'attachments' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Ticket $ticket) {
            foreach ($ticket->attachments ?? [] as $path) {
                Storage::disk('public')->delete($path);
            }
        });
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->orderBy('created_at', 'DESC');
    }

    public function routeNotificationForHelp(): string
    {
        return $this->type->notification_channel ?? config('aod.admin-ticketing-channel');
    }

    public function scopeNew(Builder $query): void
    {
        $query->where('state', 'new');
    }

    public function scopeAssigned(Builder $query): void
    {
        $query->where('state', 'assigned');
    }

    public function scopeResolved(Builder $query): void
    {
        $query->where('state', 'resolved');
    }

    public function getStateColorAttribute(): string
    {
        return $this->stateColors[$this->state];
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function hasExternalMessageId(): bool
    {
        return ! empty($this->external_message_id);
    }

    public function ownTo(Authenticatable $user): void
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

    public function resolve(): void
    {
        $this->state       = 'resolved';
        $this->owner_id    = auth()->id();
        $this->resolved_at = now();
        $this->save();
        $this->say('resolved the ticket');
    }

    public function reopen(): void
    {
        $this->state       = 'assigned';
        $this->resolved_at = null;
        $this->save();
        $this->say('reopened the ticket');
    }

    public function reject(): void
    {
        $this->state       = 'rejected';
        $this->resolved_at = now();
        $this->owner_id    = auth()->id();
        $this->save();
        $this->say('rejected the ticket');
    }

    public function say(string $comment): void
    {
        $this->comments()->create([
            'user_id' => auth()->id(),
            'body'    => $comment,
        ]);
    }
}
