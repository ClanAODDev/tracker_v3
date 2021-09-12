<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Leave.
 */
class Leave extends \Illuminate\Database\Eloquent\Model
{
    /**
     * @var array
     */
    protected $casts = ['extended'];
    /**
     * @var array
     */
    protected $dates = ['end_date'];
    /**
     * @var array
     */
    protected $fillable = ['reason', 'end_date', 'extended'];

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
    public function note()
    {
        return $this->belongsTo(\App\Models\Note::class);
    }

    /**
     * @return BelongsTo
     */
    public function requester()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * @return BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * @return bool
     */
    public function getExpiredAttribute()
    {
        return \Carbon\Carbon::today() > $this->end_date->format('Y-m-d');
    }

    /**
     * Returns end date in a short format.
     *
     * @return mixed
     */
    public function getDateAttribute()
    {
        return $this->end_date->format('Y-m-d');
    }
}
