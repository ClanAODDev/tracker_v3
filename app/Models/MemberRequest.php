<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberRequest extends \Illuminate\Database\Eloquent\Model
{
    protected $appends = ['approvePath', 'timeWaiting', 'name', 'isPastGracePeriod'];

    protected $guarded = [];

    protected $dates = ['approved_at', 'cancelled_at', 'processed_at', 'hold_placed_at'];

    /**
     * @return BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(\App\Models\Member::class, 'member_id', 'clan_id');
    }

    /**
     * @return BelongsTo
     */
    public function requester()
    {
        return $this->belongsTo(\App\Models\Member::class, 'requester_id', 'clan_id');
    }

    /**
     * @return BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(\App\Models\Member::class, 'approver_id', 'clan_id');
    }

    /**
     * @return BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(\App\Models\Division::class);
    }

    public function scopeOnHold($query)
    {
        return $query->where('hold_placed_at', '!=', null)->where('approved_at', null);
    }

    public function isOnHold()
    {
        return $this->hold_placed_at;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopePending($query)
    {
        return $query->where('approved_at', null)->where('cancelled_at', null)->where('hold_placed_at', null);
    }

    public function getIsPastGracePeriodAttribute()
    {
        return $this->created_at <= now()->subHours(2);
    }

    public function scopePastGracePeriod($query)
    {
        return $query->where('created_at', '<=', now()->subHours(2));
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeApproved($query)
    {
        return $query->where('approved_at', '!=', null)
            ->where('processed_at', null);
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return null !== $this->approved_at;
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return null !== $this->cancelled_at;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeCancelled($query)
    {
        return $query->where('cancelled_at', '!=', null);
    }

    /**
     * @return string
     */
    public function getApprovePathAttribute()
    {
        return approveMemberPath($this);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeErrors($query)
    {
        return $query->where('approved_at', '<=', now()->subHour(4))->where('processed_at', null);
    }

    /**
     * Approve a member request.
     */
    public function approve()
    {
        $this->update([
            'approver_id' => auth()->user()->member->clan_id, 'approved_at' => now(), 'cancelled_at' => null,
        ]);
    }

    /**
     * Cancel a member request.
     *
     * @param $notes
     */
    public function cancel()
    {
        $this->update([
            'cancelled_at' => now(), 'canceller_id' => auth()->user()->member->clan_id, 'notes' => request('notes'),
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
    public function canceller()
    {
        return $this->belongsTo(\App\Models\Member::class, 'canceller_id', 'clan_id');
    }

    /**
     * mark a request processed.
     */
    public function process()
    {
        $this->update(['processed_at' => now()]);
    }

    public function placeOnHold($notes)
    {
        $this->update(['hold_placed_at' => now(), 'approver_id' => auth()->user()->member->clan_id, 'notes' => $notes]);
    }

    public function removeHold()
    {
        $this->update(['hold_placed_at' => null, 'approver_id' => null]);
    }
}
