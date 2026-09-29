<?php

namespace App\Traits;

use App\Models\Division;
use App\Models\Member;

trait HasRecruitmentFields
{
    protected function buildAssignmentField(Member $member): array
    {
        return [
            'name' => sprintf(
                '%s / %s',
                $member->division->locality('platoon'),
                $member->division->locality('squad')
            ),
            'value' => $member->squad
                ? sprintf('%s / %s', $member->platoon->name, $member->squad->name)
                : 'Unassigned',
        ];
    }

    protected function buildHandleField(Member $member, ?Division $division = null): array
    {
        $division ??= $member->division;
        $handles = $division->handlesOf($member);

        if ($division->handles->count() > 1) {
            return [
                'name'  => 'In-Game Handles',
                'value' => $handles->isEmpty()
                    ? 'N/A'
                    : $handles->map(fn ($handle) => "{$handle->label}: {$handle->pivot->value}")->implode("\n"),
            ];
        }

        return [
            'name'  => $division->handles->first()->label ?? 'In-Game Handle',
            'value' => $handles->first()?->pivot?->value ?? 'N/A',
        ];
    }
}
