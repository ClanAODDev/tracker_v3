<?php

namespace App\Models;

use App\Enums\TagVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DivisionTag extends Model
{
    protected $fillable = [
        'division_id',
        'name',
        'visibility',
    ];

    protected $casts = [
        'visibility' => TagVisibility::class,
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_tag')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('division_id');
    }

    public function scopeByDivision(Builder $query, int $divisionId): Builder
    {
        return $query->where('division_id', $divisionId);
    }

    public function scopeForDivision(Builder $query, int $divisionId): Builder
    {
        return $query->where(function ($q) use ($divisionId) {
            $q->where('division_id', $divisionId)
                ->orWhereNull('division_id');
        })->orderByRaw('division_id IS NULL')->orderBy('name');
    }

    public function isGlobal(): bool
    {
        return $this->division_id === null;
    }

    public function scopeVisibleTo(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return $query->where('visibility', TagVisibility::PUBLIC);
        }

        if ($user->isRole(['admin', 'sr_ldr'])) {
            return $query;
        }

        if ($user->isRole('officer')) {
            return $query->whereIn('visibility', [
                TagVisibility::PUBLIC,
                TagVisibility::OFFICERS,
            ]);
        }

        return $query->where('visibility', TagVisibility::PUBLIC);
    }

    public function scopeAssignableBy(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isRole(['admin', 'sr_ldr'])) {
            return $query;
        }

        if ($user->isRole('officer')) {
            return $query->whereIn('visibility', [
                TagVisibility::PUBLIC,
                TagVisibility::OFFICERS,
            ]);
        }

        return $query->whereRaw('1 = 0');
    }
}
