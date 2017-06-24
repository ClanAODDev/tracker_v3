<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{

    protected $casts = [
        'extended'
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];

    /**
     * @var array
     */
    protected $guarded = ['id'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function note()
    {
        return $this->belongsTo(Note::class);
    }
}
