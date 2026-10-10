<?php

namespace App\Models\Member;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

trait HasUnits
{
    public function ledUnit(): HasOne
    {
        return $this->hasOne(Unit::class, 'leader_id', 'clan_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function unitAt(int $depth): ?Unit
    {
        $unit = $this->unit;

        while ($unit !== null && $unit->depth > $depth) {
            $unit = $unit->parent;
        }

        return $unit?->depth === $depth ? $unit : null;
    }

    public function platoonUnit(): ?Unit
    {
        return $this->unitAt(1);
    }

    public function squadUnit(): ?Unit
    {
        return $this->unit?->depth >= 2 ? $this->unit : null;
    }

    public function unitTrail(): Collection
    {
        $trail = collect();

        for ($unit = $this->unit; $unit !== null; $unit = $unit->parent) {
            $trail->prepend($unit);
        }

        return $trail;
    }
}
