<?php

namespace App;

use App\Activities\RecordsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'clan_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
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
