<?php

namespace App\Http\Controllers;

use App\Division;
use App\Http\Requests\CreatePlatoonForm;
use App\Http\Requests\DeletePlatoonForm;
use App\Http\Requests\UpdatePlatoonForm;
use App\Member;
use App\Platoon;
use App\Repositories\PlatoonRepository;
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
     */
    public function create(Division $division)
    {
        $this->authorize('create', [Platoon::class, $division]);

        $division->load('unassigned.rank');

        $lastSort = Platoon::whereDivisionId($division->id)
            ->latest()->first();

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
     * @return \Illuminate\Http\Response
     */
    public function show(Division $division, Platoon $platoon)
    {
        $members = $platoon->members()
            ->with('rank', 'position')->get()
            ->sortByDesc('rank_id');

        $activityGraph = $this->activityGraphData($platoon);

        return view(
            'platoon.show',
            compact('platoon', 'members', 'division', 'activityGraph')
        );
    }

    /**
     * Generates data for platoon activity
     *
     * @param Platoon $platoon
     * @return mixed
     */
    private function activityGraphData(Platoon $platoon)
    {
        $data = $this->platoon->getPlatoonActivity($platoon);

        return $data;
    }

    /**
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function squads(Division $division, Platoon $platoon)
    {
        $activityGraph = $this->activityGraphData($platoon);

        $squads = $platoon->squads()
            ->with(
                'members', 'members.rank', 'members.position',
                'leader', 'leader.rank', 'leader.position'
            )->get()->sortByDesc('members.rank_id');

        $unassigned = $platoon->unassigned()
            ->with('rank', 'position')
            ->get();

        return view('platoon.squads', compact(
            'platoon', 'division', 'squads',
            'unassigned', 'activityGraph'
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
            'squads', 'squads.members', 'squads.leader',
            'squads.leader.rank', 'squads.members.rank'
        );

        $platoon->squads = $platoon->squads->each(function ($squad) {
            $squad->members = $squad->members->filter(function ($member) {
                return $member->position_id === 1;
            });
        });

        return view('division.manage-members', compact('division', 'platoon'));
    }
}
