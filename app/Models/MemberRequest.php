<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberRequest extends Model
{
    protected $appends = ['approvePath', 'timeWaiting', 'name', 'isPastGracePeriod'];

    protected $guarded = [];

    protected $casts = [
        'approved_at' => 'datetime',
        'hold_placed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'clan_id');
    }

    /**
     * @return BelongsTo
     */
    public function requester()
    {
        return $this->belongsTo(Member::class, 'requester_id', 'clan_id');
    }

    /**
     * @return BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(Member::class, 'approver_id', 'clan_id');
    }

    /**
     * @return BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function scopeOnHold($query)
    {
        $query->where('hold_placed_at', '!=', null)->where('approved_at', null);
    }

    public function isOnHold()
    {
        return (bool) $this->hold_placed_at;
    }

    /**
     * @return mixed
     */
    public function scopePending($query)
    {
        $query->where('approved_at', null)->where('hold_placed_at', null);
    }

    public function getIsPastGracePeriodAttribute()
    {
        return $this->created_at <= now()->subHours(2);
    }

    public function scopePastGracePeriod($query)
    {
        $query->where('created_at', '<=', now()->subHours(2));
    }

    /**
     * @return mixed
     */
    public function scopeApproved($query)
    {
        $query->where('approved_at', '!=', null);
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return $this->approved_at !== null;
    }

    /**
     * @return string
     */
    public function getApprovePathAttribute()
    {
        return approveMemberPath($this);
    }

    /**
     * Approve a member request.
     */
    public function approve()
    {
        $this->update([
            'approver_id' => auth()->user()->member->clan_id, 'approved_at' => now(),
        ]);
    }

    public function getTimeWaitingAttribute()
    {
        return $this->created_at->diffForHumans(null, true);
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return 'AOD_' . $this->member->name;
    }

    /**
     * @return BelongsTo
     */
    public function holder()
    {
        return $this->belongsTo(Member::class, 'holder_id', 'clan_id');
    }

    public function placeOnHold($notes)
    {
        $this->update([
            'hold_placed_at' => now(),
            'holder_id' => auth()->user()->member->clan_id,
            'notes' => $notes,
        ]);
    }

    public function removeHold()
    {
        $this->update(['hold_placed_at' => null, 'approver_id' => null]);
    }
}
