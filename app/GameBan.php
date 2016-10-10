<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameBan extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id',
        'division_id',
        'bannedUser',
        'ip_address',
        'ea_guid',
        'pb_guid',
        'reason',
    ];

    /**
     * relationship - ban has one member
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * relationship - ban has one division
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
