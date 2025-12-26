<?php

namespace App\Traits;

use App\Models\Member;

trait HasRecruitmentFields
{
    protected function getMemberHandle(Member $member): ?object
    {
        return $member->handles
            ->filter(fn ($handle) => $handle->id === $member->division->handle_id)
            ->first();
    }

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

    protected function buildHandleField(Member $member): array
    {
        $handle = $this->getMemberHandle($member);

        return [
            'name' => $handle->label ?? 'In-Game Handle',
            'value' => $handle?->pivot?->value ?? 'N/A',
        ];
    }
}
