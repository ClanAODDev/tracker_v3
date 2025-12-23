<?php

namespace App\Http\Controllers;

use App\Enums\Position;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use App\Repositories\PlatoonRepository;
use Closure;
use Illuminate\Http\Response;

class PlatoonController extends Controller
{
    /**
     * PlatoonController constructor.
     */
    public function __construct(PlatoonRepository $platoon)
    {
        $this->platoon = $platoon;

        $this->middleware('auth');
    }

    /**
     * @param  CreatePlatoonForm  $request
     * @return bool
     */
    public function isMemberOfDivision(Division $division, $request)
    {
        $member = Member::whereClanId($request->leader_id)->first();

        return $member->division instanceof Division
            && $member->division->id === $division->id;
    }

    public function show(Division $division, Platoon $platoon)
    {
        $platoon->load('squads.leader');

        $members = $platoon->members()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division),
            'leave',
            'tags.division',
        ])->get()->sortByDesc('rank');

        $members = $members->each($this->getMemberHandle());

        $voiceActivityGraph = $this->platoon->getPlatoonVoiceActivity($platoon);

        return view('platoon.show', compact(
            'platoon',
            'members',
            'division',
            'voiceActivityGraph',
        ));
    }

    public function manageSquads($division, $platoon)
    {
        $platoon->load(
            'squads',
            'squads.members',
            'squads.leader',
        );

        $platoon->squads = $platoon->squads->each(function ($squad) {
            $squad->members = $squad->members->filter(fn ($member
            ) => $member->position === Position::MEMBER)->sortbyDesc(function (
                $member
            ) use ($squad) {
                return $squad->leader && $squad->leader->clan_id === $member->recruiter_id;
            });
        });

        return view('platoon.manage-members', compact('division', 'platoon'));
    }

    /**
     * @return Closure
     */
    private function filterHandlesToPrimaryHandle($division)
    {
        return function ($query) use ($division) {
            $query->where('handles.id', $division->handle_id)
                ->wherePivot('primary', true);
        };
    }

    /**
     * @return Closure
     */
    private function getMemberHandle()
    {
        return function ($member) {
            $member->handle = $member->handles->first();
        };
    }
}
