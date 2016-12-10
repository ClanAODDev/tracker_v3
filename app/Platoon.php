<?php

namespace App;

use App\Activities\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Platoon extends Model
{

    protected $dates = [
        'deleted_at'
    ];

    use RecordsActivity;
    use SoftDeletes;

    /**
     * relationship - platoon belongs to a division
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * relationship - platoon has many squads
     */
    public function squads()
    {
        return $this->hasMany(Squad::class);
    }

    /**
     * relationship - platoon has many members
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    /**
     * Leader of a platoon
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leader()
    {
        return $this->belongsTo(Member::class, 'leader_id', 'clan_id');
    }

    public function unassigned()
    {
        return $this->members()
            ->whereSquadId(0)
            ->whereIn('position_id', [1]);
    }
}



