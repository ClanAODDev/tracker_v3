<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSquadForm;
use App\Http\Requests\DeleteSquadForm;
use App\Http\Requests\UpdateSquadForm;
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
        $members = $squad->members()->with(['handles' => $this->filterHandlesToPrimaryHandle($division), 'rank', 'position', 'leave'])->get()->sortByDesc('rank_id');

        $members = $members->each($this->getMemberHandle());

        $forumActivityGraph = $this->squadRepository->getSquadForumActivity($squad);

        $tsActivityGraph = $this->squadRepository->getSquadTSActivity($squad);

        return view('squad.show', compact('squad', 'platoon', 'members', 'division', 'forumActivityGraph', 'tsActivityGraph'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Division $division, Platoon $platoon)
    {
        $this->authorize('create', [Squad::class, $division]);

        return view('squad.create', compact('division', 'platoon'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateSquadForm $form, Division $division, Platoon $platoon)
    {
        if ($form->leader_id && ! $this->isMemberOfDivision($division, $form)) {
            return redirect()->back()->withErrors(['leader_id' => "Member {$form->leader_id} not assigned to this division!"])->withInput();
        }

        $form->persist();

        $this->showToast(ucwords($division->locality('squad')) . ' has been created!');

        return redirect()->route('platoon', [$division->abbreviation, $platoon]);
    }

    public function isMemberOfDivision(Division $division, $request): bool
    {
        $member = \App\Models\Member::whereClanId($request->leader_id)->first();

        return $member->division instanceof Division && $member->division->id === $division->id;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Division $division, Platoon $platoon, Squad $squad)
    {
        $this->authorize('update', $squad);

        return view('squad.edit', compact('squad', 'platoon', 'division'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSquadForm $form, Division $division, Platoon $platoon, Squad $squad)
    {
        if ($form->leader_id && ! $this->isMemberOfDivision($division, $form)) {
            return redirect()->back()->withErrors(['leader_id' => "Member {$form->leader_id} not assigned to this division!"])->withInput();
        }

        $form->persist();

        $this->showToast('Squad has been updated');

        return redirect()->route('squad.show', [$division->abbreviation, $platoon, $squad]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteSquadForm $form, Division $division, Platoon $platoon)
    {
        $form->persist();

        $this->showToast('Squad has been deleted');

        return redirect()->route('platoon', [$division->abbreviation, $platoon]);
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
            'rank',
            'position',
            'leave',
        ])->get()->sortByDesc('rank_id');

        $members = $members->each($this->getMemberHandle());

        $csv_data = $members->reduce(function ($data, $member) {
            $data[] = [
                $member->name,
                $member->rank->abbreviation,
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
