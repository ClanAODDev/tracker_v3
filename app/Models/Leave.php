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
    protected $casts = [
        'extended' => 'boolean',
        'end_date' => 'datetime',
    ];

    public static array $reasons = [
        'military' => 'Military',
        'medical' => 'Medical',
        'education' => 'Education',
        'travel' => 'Travel',
        'other' => 'Other',
    ];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'clan_id');
    }

    public function division()
    {
        return $this->hasOneThrough(Member::class, Division::class, 'id', 'id', 'clan_id', 'division_id');
    }

    /**
     * @return BelongsTo
     */
    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * @return BelongsTo
     */
    public function requester()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(User::class);
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
