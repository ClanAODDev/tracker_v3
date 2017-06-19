<?php

namespace App;

use App\Activities\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Platoon extends Model
{

    protected $dates = [
        'deleted_at'
    ];

    protected $fillable = [
        'name',
        'leader_id',
        'logo'
    ];

    protected $with = [
        'leader'
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
     * Leader of a platoon
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leader()
    {
        return $this->belongsTo(Member::class, 'leader_id', 'clan_id');
    }

    /**
     * Only return members who are squad members
     *
     * @return Collection
     */
    public function unassigned()
    {
        return $this->members()
            ->whereSquadId(0)
            ->whereIn('position_id', [1]);
    }

    /**
     * relationship - platoon has many members
     */
    public function members()
    {
        return $this->hasMany(Member::class)
            ->orderBy('name', 'asc');
    }
}
