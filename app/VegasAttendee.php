<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VegasAttendee extends Model
{
    public $table = 'opt_in';

    public $with = ['member'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo('App\Member', 'member_id', 'clan_id');
    }
}
