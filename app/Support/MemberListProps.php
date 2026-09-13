<?php

namespace App\Support;

use App\Data\UnitStatsData;
use App\Models\Division;
use App\Models\DivisionTag;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Collection;

class MemberListProps
{
    public static function build(
        Division $division,
        Collection $members,
        UnitStatsData $unitStats,
        string $assignmentKind = 'platoon',
        ?int $directRecruitOfClanId = null,
    ): array {
        $user       = auth()->user();
        $serializer = new MemberRowSerializer($division, $assignmentKind, $directRecruitOfClanId);

        return [
            'division' => [
                'name'         => $division->name,
                'slug'         => $division->slug,
                'platoonLabel' => $division->locality('Platoon'),
                'squadLabel'   => $division->locality('Squad'),
            ],
            'members'        => $serializer->collection($members),
            'assignmentKind' => $assignmentKind,
            'unitStats'      => [
                'totalCount'     => $unitStats->totalCount,
                'onLeaveCount'   => $unitStats->onLeaveCount,
                'inactiveCount'  => $unitStats->inactiveCount,
                'inactivityDays' => $unitStats->inactivityDays,
                'avgTenureYears' => $unitStats->avgTenureYears,
                'officerCount'   => $unitStats->officerCount,
                'memberCount'    => $unitStats->memberCount,
                'voiceActivity'  => $unitStats->voiceActivityGraph,
            ],
            'tagFilter' => self::tagFilterOptions($division),
            'bulk'      => self::bulkConfig($division, $user),
        ];
    }

    private static function tagFilterOptions(Division $division): array
    {
        return DivisionTag::forDivision($division->id)
            ->visibleTo()
            ->withCount(['members' => fn ($query) => $query->where('division_id', $division->id)])
            ->get()
            ->map(fn ($tag) => [
                'id'    => $tag->id,
                'name'  => $tag->name,
                'count' => $tag->members_count,
            ])
            ->all();
    }

    private static function bulkConfig(Division $division, $user): array
    {
        $canRemind      = $user->can('remindActivity', Member::class);
        $canAssignTags  = $user->can('assign', DivisionTag::class);
        $canMoveMembers = $user->can('manageUnassigned', User::class);
        $canUseBulkMode = $user->isRole(['officer', 'sr_ldr', 'admin']) || $user->isDeveloper();

        return [
            'enabled'        => $canUseBulkMode,
            'canRemind'      => $canRemind,
            'canAssignTags'  => $canAssignTags,
            'canMoveMembers' => $canMoveMembers,
            'assignableTags' => $canAssignTags
                ? DivisionTag::forDivision($division->id)->assignableBy()->get()
                    ->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->name, 'visibility' => $tag->visibility->value])
                    ->all()
                : [],
            'urls' => [
                'pm'           => route('private-message.create', ['division' => $division]),
                'tags'         => route('bulk-tags.store', $division),
                'transfer'     => route('bulk-transfer.store', $division),
                'transferData' => route('bulk-transfer.platoons', $division),
                'reminder'     => route('bulk-reminder.store', $division),
            ],
        ];
    }
}
