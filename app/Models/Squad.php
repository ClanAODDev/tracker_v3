<?php

namespace App\Models;

use App\Activities\RecordsActivity;
use App\Enums\ActivityType;
use App\Presenters\SquadPresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
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

    public function present(): SquadPresenter
    {
        return new SquadPresenter($this);
    }

    public function platoon(): BelongsTo
    {
        return $this->belongsTo(Platoon::class);
    }

    public function division(): HasOneThrough
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

    public function members(): HasMany
    {
        return $this->hasMany(Member::class)
            ->orderBy('rank', 'desc')
            ->orderBy('name');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'leader_id', 'clan_id');
    }

    public function assignLeaderTo(Member $member): self
    {
        $this->leader()->associate($member);

        return $this;
    }
}
