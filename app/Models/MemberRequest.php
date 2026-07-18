<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberRequest extends Model
{
    use HasFactory;

    protected $appends = ['approvePath', 'timeWaiting', 'name', 'isPastGracePeriod'];

    protected $guarded = [];

    protected $casts = [
        'approved_at'    => 'datetime',
        'hold_placed_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'requester_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'approver_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function holder(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'holder_id');
    }

    public function scopeOnHold(Builder $query): void
    {
        $query->whereNotNull('hold_placed_at')->whereNull('approved_at');
    }

    public function scopePending(Builder $query): void
    {
        $query->whereNull('approved_at')->whereNull('hold_placed_at');
    }

    public function scopeApproved(Builder $query): void
    {
        $query->whereNotNull('approved_at');
    }

    public function scopePastGracePeriod(Builder $query): void
    {
        $query->where('created_at', '<=', now()->subHours(2));
    }

    public function isOnHold(): bool
    {
        return (bool) $this->hold_placed_at;
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function getIsPastGracePeriodAttribute(): bool
    {
        return $this->created_at <= now()->subHours(2);
    }

    public function getApprovePathAttribute(): string
    {
        return approveMemberPath($this);
    }

    public function getTimeWaitingAttribute(): string
    {
        return $this->created_at->diffForHumans(null, true);
    }

    public function getNameAttribute(): string
    {
        return 'AOD_' . $this->member->name;
    }

    public function approve(): void
    {
        $this->update([
            'approver_id' => auth()->user()->member_id,
            'approved_at' => now(),
        ]);
    }

    public function placeOnHold(string $notes): void
    {
        $this->update([
            'hold_placed_at' => now(),
            'holder_id'      => auth()->user()->member_id,
            'notes'          => $notes,
        ]);
    }

    public function removeHold(): void
    {
        $this->update(['hold_placed_at' => null, 'approver_id' => null]);
    }
}
