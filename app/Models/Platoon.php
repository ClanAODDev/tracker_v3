<?php

namespace App\Models;

use App\Activities\RecordsActivity;
use App\Enums\ActivityType;
use App\Enums\Position;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Platoon extends Model
{
    use HasFactory;
    use RecordsActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'leader_id',
        'division_id',
        'logo',
        'order',
    ];

    protected $with = [
        'leader',
    ];

    protected static function booted(): void
    {
        static::created(fn (Platoon $platoon) => $platoon->recordActivity(ActivityType::CREATED_PLATOON));
        static::updated(fn (Platoon $platoon) => $platoon->recordActivity(ActivityType::UPDATED_PLATOON));
        static::deleted(fn (Platoon $platoon) => $platoon->recordActivity(ActivityType::DELETED_PLATOON));
    }

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
            ->whereNotIn('position', [Position::PLATOON_LEADER]);
    }

    /**
     * relationship - platoon has many members.
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function getLogoPath()
    {
        if ($this->logo) {
            if (str_starts_with($this->logo, 'http')) {
                return $this->logo;
            }

            if (Storage::disk('public')->exists($this->logo)) {
                return asset(Storage::url($this->logo));
            }
        }

        return $this->division->getLogoPath();
    }
}
