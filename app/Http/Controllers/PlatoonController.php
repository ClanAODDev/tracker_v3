<?php

namespace App\Http\Controllers;

use App\Division;
use App\Http\Requests\CreatePlatoonForm;
use App\Http\Requests\DeletePlatoonForm;
use App\Http\Requests\UpdatePlatoonForm;
use App\Member;
use App\Platoon;
use App\Repositories\PlatoonRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Toastr;

class PlatoonController extends Controller
{
    /**
     * PlatoonController constructor.
     *
     * @param PlatoonRepository $platoon
     */
    public function __construct(PlatoonRepository $platoon)
    {
        $this->platoon = $platoon;

        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Division $division
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Division $division)
    {
        $this->authorize('create', [Platoon::class, $division]);

        $division->load('unassigned.rank');

        $lastSort = Platoon::whereDivisionId($division->id)
            ->orderByDesc('id')
            ->first();

        $lastSort = ($lastSort) ? $lastSort->order + 100 : 100;

        return view('platoon.create', compact('division', 'lastSort'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreatePlatoonForm $form
     * @param Division $division
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreatePlatoonForm $form, Division $division)
    {
        if ($form->leader_id && ! $this->isMemberOfDivision($division, $form)) {
            return redirect()->back()
                ->withErrors(['leader' => "Member {$form->leader_id} not to this division!"])
                ->withInput();
        }

        $form->persist();

        $this->showToast("{$division->locality('platoon')} has been created!");

        return redirect()->route('division', $division->abbreviation);
    }

    /**
     * @param CreatePlatoonForm $request
     * @param Division $division
     * @return bool
     */
    public function isMemberOfDivision(Division $division, $request)
    {
        $member = Member::whereClanId($request->leader_id)->first();

        return $member->division instanceof Division &&
            $member->division->id === $division->id;
    }

    /**
     * Display the specified resource.
     *
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Http\Response|StreamedResponse
     */
    public function show(Division $division, Platoon $platoon)
    {
        $members = $platoon->members()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division),
            'rank',
            'position',
            'leave'
        ])->get()->sortByDesc('rank_id');

        $members = $members->each($this->getMemberHandle());

        $forumActivityGraph = $this->platoon->getPlatoonForumActivity($platoon);
        $tsActivityGraph = $this->platoon->getPlatoonTSActivity($platoon);

        return view(
            'platoon.show',
            compact('platoon', 'members', 'division', 'forumActivityGraph', 'tsActivityGraph')
        );
    }


    /**
     * @param $division
     * @return \Closure
     */
    private function filterHandlesToPrimaryHandle($division)
    {
        return function ($query) use ($division) {
            $query->where('id', $division->handle_id);
        };
    }

    /**
     * @return \Closure
     */
    private function getMemberHandle()
    {
        return function ($member) {
            $member->handle = $member->handles->first();
        };
    }

    /**
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function squads(Division $division, Platoon $platoon)
    {
        $forumActivityGraph = $this->platoon->getPlatoonForumActivity($platoon);
        $tsActivityGraph = $this->platoon->getPlatoonTSActivity($platoon);

        $squads = $platoon->squads()
            ->with([
                'members.handles' => $this->filterHandlesToPrimaryHandle($division),
                'members',
                'members.rank',
                'members.position',
                'members.leave',
                'leader',
                'leader.rank',
                'leader.position'
            ])->get()->sortByDesc('members.rank_id');

        $unassigned = $platoon->unassigned()
            ->with('rank', 'position')
            ->get();

        return view('platoon.squads', compact(
            'platoon', 'division', 'squads', 'unassigned',
            'forumActivityGraph', 'tsActivityGraph'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Http\Response
     */
    public function edit(Division $division, Platoon $platoon)
    {
        $this->authorize('update', $platoon);

        $division->load('unassigned.rank')->withCount('unassigned')->get();

        return view('platoon.edit', compact('division', 'platoon', 'lastSort'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePlatoonForm $form
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function update(UpdatePlatoonForm $form, Division $division, Platoon $platoon)
    {
        if ($form->leader_id && ! $this->isMemberOfDivision($division, $form)) {
            return redirect()->back()
                ->withErrors(['leader_id' => "Member {$form->leader_id} not assigned to this division!"])
                ->withInput();
        }

        $toastMessage = "Your changes were saved";

        if ($form->member_ids) {
            $assignedCount = count(json_decode($form->member_ids));
            $toastMessage .= " and {$assignedCount} members were assigned!";
        }

        $form->persist($platoon);

        $this->showToast($toastMessage);

        return redirect()->route('platoon', [$division->abbreviation, $platoon]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePlatoonForm $form
     * @param Division $division
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(DeletePlatoonForm $form, Division $division)
    {
        $form->persist();

        $this->showToast('Platoon has been deleted');

        return redirect()->route('division', $division->abbreviation);
    }

    public function manageSquads($division, $platoon)
    {
        $platoon->load(
            'squads', 'squads.members', 'squads.leader', 'unassigned.rank',
            'squads.leader.rank', 'squads.members.rank'
        );

        $platoon->squads = $platoon->squads->each(function ($squad) {
            $squad->members = $squad->members->filter(function ($member) {
                return $member->position_id === 1;
            });
        });

        return view('platoon.manage-members', compact('division', 'platoon'));
    }

    /**
     * Export platoon members as CSV
     *
     * @param Division $division
     * @param Platoon $platoon
     * @return StreamedResponse
     */
    public function exportAsCSV(Division $division, Platoon $platoon)
    {
        $members = $platoon->members()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division),
            'rank',
            'position',
            'leave'
        ])->get()->sortByDesc('rank_id');

        $members = $members->each($this->getMemberHandle());

        $csv_data = $members->reduce(function ($data, $member) {
            $data[] = [
                $member->name,
                $member->rank->abbreviation,
                $member->join_date,
                $member->last_activity,
                $member->last_ts_activity,
                $member->last_promoted,
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
                'Last TS Activity',
                'Last Promoted',
                'Member Handle',
                'Member Forum Posts'
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
}
