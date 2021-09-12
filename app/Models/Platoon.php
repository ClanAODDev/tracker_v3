<?php

namespace App\Models;

use App\Activities\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Platoon extends Model
{
    use HasFactory;
    use RecordsActivity;
    protected $fillable = [
        'name',
        'leader_id',
        'logo',
        'order',
    ];

    protected $with = [
        'leader',
    ];

    /**
     * relationship - platoon belongs to a division.
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * relationship - platoon has many squads.
     */
    public function squads()
    {
        return $this->hasMany(Squad::class);
    }

    /**
     * Leader of a platoon.
     *
     * @return BelongsTo
     */
    public function leader()
    {
        return $this->belongsTo(Member::class, 'leader_id', 'clan_id');
    }

    /**
     * Only return members who are squad members.
     *
     * @return Collection
     */
    public function unassigned()
    {
        return $this->members()
            ->whereSquadId(0)
            ->whereNotIn('position_id', [3]);
    }

    /**
     * relationship - platoon has many members.
     */
    public function members()
    {
        return $this->hasMany(Member::class)
            ->orderBy('rank_id', 'desc')
            ->orderBy('name', 'asc');
    }
}
