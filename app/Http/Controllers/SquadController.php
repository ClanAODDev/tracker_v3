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
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $members = $squad->members()->with(['handles' => $this->filterHandlesToPrimaryHandle($division), 'leave'])->get()->sortByDesc('rank');

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

    /**
     * Export platoon members as CSV.
     */
    public function exportAsCSV(Division $division, Platoon $platoon, Squad $squad): StreamedResponse
    {
        $members = $squad->members()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division),
            'leave',
        ])->get()->sortByDesc('rank');

        $members = $members->each($this->getMemberHandle());

        $csv_data = $members->reduce(function ($data, $member) {
            $data[] = [
                $member->name,
                $member->rank->getAbbreviation(),
                $member->join_date,
                $member->last_activity,
                $member->last_ts_activity,
                $member->last_promoted_at,
                $member->handle->pivot->value ?? 'N/A',
                $member->posts,
            ];

            return $data;
        }, [
            [
                'Name',
                'Rank',
                'Join Date',
                'Last Forum Activity',
                'Last Comms Activity',
                'Last Promoted',
                'Member Handle',
                'Member Forum Posts',
            ],
        ]);

        return new StreamedResponse(function () use ($csv_data) {
            $handle = fopen('php://output', 'w');
            foreach ($csv_data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=members.csv',
        ]);
    }

    private function filterHandlesToPrimaryHandle($division): Closure
    {
        return function ($query) use ($division) {
            $query->where('id', $division->handle_id);
        };
    }

    private function getMemberHandle(): Closure
    {
        return function ($member) {
            $member->handle = $member->handles->first();
        };
    }
}
