<?php

namespace App\Models;

use App\Activities\RecordsActivity;
use App\Enums\ActivityType;
use App\Presenters\SquadPresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Squad extends Model
{
    use HasFactory;
    use RecordsActivity;
    use SoftDeletes;

    protected $fillable = [
        'leader_id',
        'name',
        'logo',
    ];

    protected static function booted(): void
    {
        static::created(fn (Squad $squad) => $squad->recordActivity(ActivityType::CREATED_SQUAD));
        static::updated(fn (Squad $squad) => $squad->recordActivity(ActivityType::UPDATED_SQUAD));
        static::deleted(fn (Squad $squad) => $squad->recordActivity(ActivityType::DELETED_SQUAD));
    }

    public function present()
    {
        return new SquadPresenter($this);
    }

    /**
     * relationship - squad belongs to a platoon.
     */
    public function platoon()
    {
        return $this->belongsTo(Platoon::class);
    }

    public function division()
    {
        return $this->hasOneThrough(
            Division::class,
            Platoon::class,
            'id',
            'id',
            'platoon_id',
            'division_id'
        );
    }

    /**
     * relationship - squad has many members.
     */
    public function members()
    {
        return $this->hasMany(Member::class)
            ->orderBy('rank', 'desc')
            ->orderBy('name');
    }

    /**
     * Assign the leader of a squad.
     *
     * @return Model
     */
    public function assignLeaderTo($member)
    {
        return $this->leader()->associate($member);
    }

    /**
     * Leader of a squad.
     *
     * @return BelongsTo
     */
    public function leader()
    {
        return $this->belongsTo(Member::class, 'leader_id', 'clan_id');
    }
}
