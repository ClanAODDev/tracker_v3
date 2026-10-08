<?php

namespace App\Models;

use App\Enums\UnitLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Unit extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const LEGACY_PLATOON = 'platoon';

    public const LEGACY_SQUAD = 'squad';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'gen_pop' => 'boolean',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order')->orderBy('id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'leader_id', 'clan_id');
    }

    public function isTopLevel(): bool
    {
        return $this->parent_id === null;
    }

    public function tier(): UnitLevel
    {
        return UnitLevel::forDepth($this->depth, $this->division?->deepestUnitLevel() ?? 2);
    }

    public function isPlatoon(): bool
    {
        return $this->tier() === UnitLevel::Platoon;
    }

    public function isSquad(): bool
    {
        return $this->tier() === UnitLevel::Squad;
    }

    public function level(): ?DivisionUnitLevel
    {
        return $this->division?->unitLevel($this->depth);
    }

    public function levelLabel(): string
    {
        return $this->level()?->label ?? 'Unit';
    }

    public function leaderTitle(): string
    {
        return $this->level()?->leader_title ?? 'Leader';
    }

    public function childLevel(): ?DivisionUnitLevel
    {
        return $this->division?->unitLevel($this->depth + 1);
    }

    public function scopeOfTier(Builder $query, UnitLevel $tier): Builder
    {
        $deepest = '(select coalesce(max(division_unit_levels.depth), 2) from division_unit_levels where division_unit_levels.division_id = units.division_id)';

        return $tier === UnitLevel::Platoon
            ? $query->whereRaw("(units.depth < {$deepest} or {$deepest} = 1)")
            : $query->whereRaw("(units.depth = {$deepest} and {$deepest} > 1)");
    }

    public function url(Division $division): string
    {
        return route('unit', [$division->slug, $this->id]);
    }

    public function subtreeMembers(): HasMany
    {
        return $this->hasMany(Member::class, 'division_id', 'division_id')
            ->whereIn('members.unit_id', self::query()->where('path', 'like', $this->path . '%')->select('id'));
    }

    public function allMembers(): Builder
    {
        return Member::query()->whereIn('unit_id', self::query()->where('path', 'like', $this->path . '%')->select('id'));
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
