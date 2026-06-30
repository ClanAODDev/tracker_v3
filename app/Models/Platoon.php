<?php

namespace App\Models;

use App\Activities\RecordsActivity;
use App\Enums\ActivityType;
use App\Enums\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Platoon extends Model
{
    use HasFactory;
    use RecordsActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
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

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function squads(): HasMany
    {
        return $this->hasMany(Squad::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'leader_id', 'clan_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function unassigned(): Builder
    {
        return $this->members()
            ->where('squad_id', 0)
            ->whereNotIn('position', [Position::PLATOON_LEADER]);
    }

    public function getLogoPath(): string
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
