<?php

namespace App\Services;

use App\Models\Division;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class MemberQueryService
{
    public function withStandardRelations(Builder|BelongsToMany|HasMany $query, Division $division): Builder|BelongsToMany|HasMany
    {
        return $query->with([
            'handles' => $this->divisionHandlesConstraint($division),
            'leave',
            'tags.division',
            'unit.parent',
            'fieldValues' => fn ($query) => $query->whereHas(
                'field',
                fn ($q) => $q->where('division_id', $division->id)
            ),
            'fieldValues.field',
        ]);
    }

    public function divisionHandlesConstraint(Division $division): Closure
    {
        return function ($query) use ($division) {
            $query->whereIn('handles.id', $division->handles->pluck('id'))
                ->wherePivot('primary', true);
        };
    }

    public function loadSortedMembers(Builder|BelongsToMany|HasMany $query, Division $division): Collection
    {
        return $this->withStandardRelations($query, $division)
            ->where('division_id', $division->id)
            ->get()
            ->sortByDesc(fn ($m) => $m->rank->value);
    }
}
