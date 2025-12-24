<?php

namespace App\Http\Controllers;

use App\Data\UnitStatsData;
use App\Enums\Position;
use App\Models\Division;
use App\Models\Platoon;
use App\Repositories\PlatoonRepository;
use App\Services\MemberQueryService;

class PlatoonController extends Controller
{
    public function __construct(
        private PlatoonRepository $platoon,
        private MemberQueryService $memberQuery,
    ) {
        $this->middleware('auth');
    }

    public function show(Division $division, Platoon $platoon)
    {
        $platoon->load('squads.leader', 'squads.members');

        $members = $this->memberQuery->loadSortedMembers($platoon->members(), $division);
        $voiceActivityGraph = $this->platoon->getPlatoonVoiceActivity($platoon);
        $unitStats = UnitStatsData::fromMembers($members, $division, $voiceActivityGraph);

        return view('platoon.show', compact('platoon', 'members', 'division', 'unitStats'));
    }

    public function manageSquads($division, $platoon)
    {
        $platoon->load('squads', 'squads.members', 'squads.leader');

        $platoon->squads = $platoon->squads->each(function ($squad) {
            $squad->members = $squad->members
                ->filter(fn ($member) => $member->position === Position::MEMBER)
                ->sortbyDesc(fn ($member) => $squad->leader && $squad->leader->clan_id === $member->recruiter_id);
        });

        return view('platoon.manage-members', compact('division', 'platoon'));
    }
}
