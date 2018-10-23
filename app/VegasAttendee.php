<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VegasAttendee extends Model
{
    public $table = 'opt_in';

    public $with = ['member'];

    public $fillable = ['member_id'];

    /**
     * Opt in to event
     */
    public function optIn()
    {
        $user = auth()->user();
        $this->member_id = $user->member->clan_id;
        $this->save();
    }

    /**
     * Opt out of event
     */
    public function optOut()
    {
        $this->opted_out = now();
        $this->save();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo('App\Member', 'member_id', 'clan_id');
    }
}
