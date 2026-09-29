<?php

namespace App\Models\Division;

use App\Models\DivisionHandle;
use App\Models\Handle;
use App\Models\Member;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasHandles
{
    public function handles(): BelongsToMany
    {
        return $this->belongsToMany(Handle::class)
            ->withPivot('sort_order')
            ->orderByPivot('sort_order')
            ->orderBy('handles.id');
    }

    public function handleAssignments(): HasMany
    {
        return $this->hasMany(DivisionHandle::class)->orderBy('sort_order');
    }

    public function handlesOf(Member $member): Collection
    {
        return $this->handles
            ->map(fn (Handle $type) => $member->handles
                ->where('id', $type->id)
                ->sortByDesc(fn (Handle $handle) => (bool) $handle->pivot->primary)
                ->first())
            ->filter()
            ->values();
    }

    public function handleTypes(): array
    {
        return $this->handles
            ->map(fn (Handle $handle) => ['id' => $handle->id, 'label' => $handle->label, 'hint' => $handle->regex_hint])
            ->values()
            ->all();
    }

    public function handleSummaryFor(Member $member): ?string
    {
        $handles = $this->handlesOf($member);

        if ($handles->isEmpty()) {
            return null;
        }

        if ($this->handles->count() === 1) {
            return $handles->first()->pivot->value;
        }

        return $handles->map(fn (Handle $handle) => "{$handle->label}: {$handle->pivot->value}")->implode(' · ');
    }

    public function handleFieldFor(Member $member): array
    {
        $handles = $this->handlesOf($member);

        if ($this->handles->count() > 1) {
            return [
                'name'  => 'In-Game Handles',
                'value' => $handles->isEmpty()
                    ? 'N/A'
                    : $handles->map(fn (Handle $handle) => "{$handle->label}: {$handle->pivot->value}")->implode("\n"),
            ];
        }

        return [
            'name'  => $this->handles->first()->label ?? 'In-Game Handle',
            'value' => $handles->first()?->pivot?->value ?? 'N/A',
        ];
    }
}
