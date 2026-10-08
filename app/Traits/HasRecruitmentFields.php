<?php

namespace App\Traits;

use App\Models\Member;

trait HasRecruitmentFields
{
    protected function buildAssignmentField(Member $member): array
    {
        return [
            'name'  => $member->division->unitLevels->sortBy('depth')->pluck('label')->implode(' / ') ?: 'Assignment',
            'value' => $member->unitTrail()->isEmpty()
                ? 'Unassigned'
                : $member->unitTrail()->map(fn ($unit) => $unit->name ?: 'Untitled')->implode(' / '),
        ];
    }
}
