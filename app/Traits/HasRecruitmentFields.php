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
            'value' => $member->squad
                ? sprintf('%s / %s', $member->platoon->name, $member->squad->name)
                : 'Unassigned',
        ];
    }
}
