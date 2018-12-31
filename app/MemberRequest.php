<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MemberRequest extends Model
{
    protected $appends = ['approvePath', 'timeWaiting', 'name'];

    protected $guarded = [];

    protected $dates = ['approved_at', 'denied_at', 'cancelled_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(\App\Member::class, 'member_id', 'clan_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester()
    {
        return $this->belongsTo(\App\Member::class, 'requester_id', 'clan_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(\App\Member::class, 'approver_id', 'clan_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(\App\Division::class);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopePending($query)
    {
        return $query->where('approved_at', null)
            ->where('cancelled_at', null);
    }

    public function scopePastGracePeriod($query)
    {
        return $query->where('created_at', '<=', Carbon::now()->subHour(2));
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeApproved($query)
    {
        return $query->where('approved_at', '!=', null);
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return $this->approved_at != null;
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return $this->cancelled_at != null;
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
        return $query->where('approved_at', '<=', now()->subHour(4));
    }

    /**
     * Approve a member request
     */
    public function approve()
    {
        $this->update([
            'approver_id' => auth()->user()->member->clan_id,
            'approved_at' => now(),
            'cancelled_at' => null,
        ]);
    }

    /**
     * Cancel a member request
     */
    public function cancel()
    {
        $this->update([
            'cancelled_at' => now(),
            'canceller_id' => auth()->user()->member->clan_id,
            'notes' => request('notes'),
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
        return "AOD_" . $this->member->name;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function canceller()
    {
        return $this->belongsTo(\App\Member::class, 'canceller_id', 'clan_id');
    }
}
