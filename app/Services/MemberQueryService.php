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
            'handles' => $this->primaryHandleConstraint($division),
            'leave',
            'tags.division',
            'platoon',
            'squad',
        ]);
    }

    public function primaryHandleConstraint(Division $division): Closure
    {
        return function ($query) use ($division) {
            $query->where('handles.id', $division->handle_id)
                ->wherePivot('primary', true);
        };
    }

    public function extractHandles(Collection $members): Collection
    {
        return $members->each(fn ($member) => $member->handle = $member->handles->first());
    }

    public function loadSortedMembers(Builder|BelongsToMany|HasMany $query, Division $division): Collection
    {
        return $this->extractHandles(
            $this->withStandardRelations($query, $division)->get()->sortByDesc('rank')
        );
    }
}
