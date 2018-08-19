<?php

namespace App;

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
        return $this->belongsTo('App\Member', 'member_id', 'clan_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester()
    {
        return $this->belongsTo('App\Member', 'requester_id', 'clan_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo('App\Member', 'approver_id', 'clan_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division()
    {
        return $this->belongsTo('App\Division');
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
            'cancelled_at' => now()
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
}
