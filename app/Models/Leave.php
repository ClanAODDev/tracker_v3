<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Leave
 *
 * @package App
 */
class Leave extends Model
{
    /**
     * @var array
     */
    protected $casts = [
        'extended'
    ];
    /**
     * @var array
     */
    protected $dates = [
        'end_date'
    ];
    /**
     * @var array
     */
    protected $fillable = [
        'reason',
        'end_date',
        'extended'
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
        return Carbon::today() > $this->end_date->format('Y-m-d');
    }

    /**
     * Returns end date in a short format
     *
     * @return mixed
     */
    public function getDateAttribute()
    {
        return $this->end_date->format('Y-m-d');
    }
}
