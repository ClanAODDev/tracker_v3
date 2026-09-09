<?php

namespace App\Http\Controllers;

use App\Data\UnitStatsData;
use App\Enums\ActivityType;
use App\Http\Requests\Squad\AssignSquadMemberRequest;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use App\Repositories\SquadRepository;
use App\Services\MemberQueryService;
use App\Support\MemberListProps;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class SquadController extends Controller
{
    public function __construct(
        private SquadRepository $squadRepository,
        private MemberQueryService $memberQuery,
    ) {}

    public function show(Division $division, Platoon $platoon, Squad $squad): Response
    {
        $platoon->load('squads.leader');
        $squad->loadMissing('leader');

        $members            = $this->memberQuery->loadSortedMembers($squad->members(), $division);
        $voiceActivityGraph = $this->squadRepository->getSquadVoiceActivity($squad);
        $unitStats          = UnitStatsData::fromMembers($members, $division, $voiceActivityGraph);
        $canManage          = auth()->user()->can('update', $squad);

        return Inertia::render('division/members', [
            ...MemberListProps::build(
                $division,
                $members,
                $unitStats,
                assignmentKind: 'squad',
                directRecruitOfClanId: $squad->leader?->clan_id,
            ),
            'scope' => [
                'kind'        => 'squad',
                'name'        => $squad->name ?: 'Untitled ' . $division->locality('squad'),
                'canManage'   => $canManage,
                'editUrl'     => $canManage ? route('filament.mod.resources.squads.edit', $squad) : null,
                'breadcrumbs' => [
                    ['label' => $division->name, 'href' => route('division', $division->slug)],
                    ['label' => $platoon->name ?: 'Untitled', 'href' => route('platoon', [$division->slug, $platoon->id])],
                    ['label' => $squad->name ?: 'Untitled'],
                ],
            ],
            'squads' => $platoon->squads->map(fn ($s, $i) => [
                'name'    => $s->name ?: ordSuffix($i + 1) . ' Squad',
                'url'     => route('squad.show', [$division->slug, $platoon, $s]),
                'leader'  => $s->leader?->present()->rankName(),
                'current' => $s->id === $squad->id,
            ])->values(),
        ]);
    }

    public function assignMember(AssignSquadMemberRequest $request): JsonResponse
    {
        $member = Member::findOrFail($request->member_id);

        if ((int) $request->squad_id === 0) {
            if (! $member->platoon) {
                return response()->json(['success' => true]);
            }

            $this->authorize('update', $member->platoon);

            $member->platoon()->dissociate();
            $member->squad()->dissociate();
            $member->save();
            $member->recordActivity(ActivityType::UNASSIGNED);
        } else {
            $squad = Squad::findOrFail($request->squad_id);
            $this->authorize('update', $squad->platoon);

            $member->platoon()->associate($squad->platoon);
            $member->squad()->associate($squad);
            $member->save();
            $member->recordActivity(ActivityType::ASSIGNED_SQUAD, [
                'platoon' => $squad->platoon->name,
                'squad'   => $squad->name,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
