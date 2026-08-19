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
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
class SquadController extends Controller
{
    public function __construct(
        private SquadRepository $squadRepository,
        private MemberQueryService $memberQuery,
    ) {}

    public function show(Division $division, Platoon $platoon, Squad $squad)
    {
        $platoon->load('squads.leader');

        $members            = $this->memberQuery->loadSortedMembers($squad->members(), $division);
        $voiceActivityGraph = $this->squadRepository->getSquadVoiceActivity($squad);
        $unitStats          = UnitStatsData::fromMembers($members, $division, $voiceActivityGraph);

        return view('squad.show', compact('squad', 'platoon', 'members', 'division', 'unitStats'));
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
