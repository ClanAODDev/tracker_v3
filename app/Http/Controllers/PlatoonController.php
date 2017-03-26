<?php

namespace App\Http\Controllers;

use App\Division;
use App\Http\Requests\CreatePlatoonForm;
use App\Http\Requests\UpdatePlatoonForm;
use App\Member;
use App\Platoon;
use App\Repositories\PlatoonRepository;
use Charts;
use Toastr;

class PlatoonController extends Controller
{
    /**
     * PlatoonController constructor.
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

        return view('platoon.create', compact('division'));
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

        Toastr::success(
            "{$division->locality('platoon')} has been created!",
            "Success",
            [
                'positionClass' => 'toast-top-right',
                'progressBar' => true
            ]);

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

        return $member->primaryDivision instanceof Division &&
            $member->primaryDivision->id === $division->id;
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
        $members = $platoon->members()->with(
            'rank',
            'position',
            'divisions'
        )->get();

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

        return view(
            'platoon.edit',
            compact('division', 'platoon')
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePlatoonForm $form
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePlatoonForm $form, Division $division, Platoon $platoon)
    {
        if ($form->leader_id && ! $this->isMemberOfDivision($division, $form)) {
            return redirect()->back()
                ->withErrors(['leader_id' => "Member {$form->leader_id} not assigned to this division!"])
                ->withInput();
        }

        $form->persist($platoon);

        return redirect()->route('division', $division->abbreviation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $division
     * @param $platoon
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Division $division, Platoon $platoon)
    {
        $this->authorize('delete', $platoon);

        if ($platoon->leader) {
            // dissociate leader from platoon
            $platoon->leader()->dissociate()->save();
        }

        if ($platoon->squads()) {
            $platoon->squads->each(function ($squad) use($platoon) {

                // remove members from squads inside platoon
                $squad->members->each(function ($member) use ($squad) {
                    $member->squad()->dissociate($squad)->save();
                    $member->assignPosition('member');
                });

                // dissociate squad from platoon
                $squad->platoon()->dissociate($platoon);

                $squad->delete();
            });
        }

        if ($platoon->members()) {

            // dissociate members from platoon
            $platoon->members->each(function ($member) use ($platoon) {
                $member->platoon()->dissociate($platoon)->save();
            });
        }


        $platoon->delete();

        return redirect()->route('division', $division->abbreviation);
    }
}
