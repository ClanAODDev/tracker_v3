<?php

namespace App\Models\Division;

use App\Models\DivisionUnitLevel;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasUnits
{
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function unitLevels(): HasMany
    {
        return $this->hasMany(DivisionUnitLevel::class)->orderBy('depth');
    }

    public function deepestUnitLevel(): int
    {
        return (int) ($this->unitLevels->max('depth') ?? 0);
    }

    public function isFlat(): bool
    {
        return $this->deepestUnitLevel() === 0;
    }

    public function unitLevel(int $depth): ?DivisionUnitLevel
    {
        return $this->unitLevels->firstWhere('depth', $depth);
    }

    public function bottomUnitDepth(): int
    {
        return max(2, $this->deepestUnitLevel());
    }

    public function topLevelLabel(): string
    {
        return $this->unitLevel(1)?->label ?? 'Platoon';
    }

    public function bottomLevelLabel(): string
    {
        return $this->unitLevel($this->bottomUnitDepth())?->label ?? 'Squad';
    }

    public function topLevelPlural(): string
    {
        return $this->unitLevel(1)?->label_plural ?? 'Platoons';
    }

    public function bottomLevelPlural(): string
    {
        return $this->unitLevel($this->bottomUnitDepth())?->label_plural ?? 'Squads';
    }

    public function topLeaderTitle(): string
    {
        return $this->unitLevel(1)?->leader_title ?? 'Platoon Leader';
    }

    public function bottomLeaderTitle(): string
    {
        return $this->unitLevel($this->bottomUnitDepth())?->leader_title ?? 'Squad Leader';
    }

    public function topUnits(): HasMany
    {
        return $this->units()->whereNull('parent_id')->orderBy('order')->orderBy('id');
    }
}
