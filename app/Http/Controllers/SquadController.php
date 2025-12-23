<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Platoon;
use App\Models\Squad;
use App\Repositories\SquadRepository;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SquadController extends Controller
{
    private SquadRepository $squadRepository;

    public function __construct(SquadRepository $squad)
    {
        $this->squadRepository = $squad;

        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function show(Division $division, Platoon $platoon, Squad $squad)
    {
        $platoon->load('squads.leader');

        $members = $squad->members()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division), 'leave', 'tags.division',
        ])->get()->sortByDesc('rank');

        $members = $members->each($this->getMemberHandle());
        $voiceActivityGraph = $this->squadRepository->getSquadVoiceActivity($squad);

        return view('squad.show', compact('squad', 'platoon', 'members', 'division', 'voiceActivityGraph'));
    }

    public function isMemberOfDivision(Division $division, $request): bool
    {
        $member = \App\Models\Member::whereClanId($request->leader_id)->first();

        return $member->division instanceof Division && $member->division->id === $division->id;
    }

    /**
     * Assign a member to a squad.
     */
    public function assignMember(Request $request): JsonResponse
    {
        $member = \App\Models\Member::find($request->member_id);
        // if squad id is zero, user wants to unassign member
        if ((int) $request->squad_id === 0) {
            $member->platoon()->dissociate();
            $member->squad()->dissociate();
        } else {
            // ensure they are assigned to current platoon
            $squad = Squad::find($request->squad_id);
            $member->platoon()->associate($squad->platoon);
            $member->squad()->associate($squad);
        }

        $member->save();

        return response()->json(['success' => true]);
    }

    private function filterHandlesToPrimaryHandle($division): Closure
    {
        return function ($query) use ($division) {
            $query->where('handles.id', $division->handle_id)
                ->wherePivot('primary', true);
        };
    }

    private function getMemberHandle(): Closure
    {
        return function ($member) {
            $member->handle = $member->handles->first();
        };
    }
}
