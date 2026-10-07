<?php

namespace App\Traits;

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
            'value' => ($squad = $member->squadUnit())
                ? sprintf('%s / %s', $squad->parent?->name, $squad->name)
                : 'Unassigned',
        ];
    }
}
