<?php

namespace App\Http\Controllers;

use Charts;
use Toastr;
use App\Member;
use App\Platoon;
use App\Division;
use Illuminate\Http\Request;
use App\Repositories\PlatoonRepository;
use App\Http\Requests\CreatePlatoonForm;

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
        if ($form->leader && ! $this->isMemberOfDivision($division, $form)) {
            return redirect()->back()
                ->withErrors(['leader' => 'Member not in your division!'])
                ->withInput();
        }

        $form->persist();

        Toastr::success(
            "{$division->locality('platoon')} has been created! If you assigned a leader, be sure to update their account access as needed.",
            "New {$division->locality('platoon')} Created",
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
    public function isMemberOfDivision(Division $division, CreatePlatoonForm $request)
    {
        $member = Member::whereClanId($request->leader)->first();

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
     * Show the form for editing the specified resource.
     *
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Http\Response
     */
    public function edit(Division $division, Platoon $platoon)
    {
        $this->authorize('update', [Platoon::class, $division]);

        return view(
            'platoon.edit',
            compact('division', 'platoon')
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Division $division, Platoon $platoon)
    {
        $this->authorize('update', [Platoon::class, $division]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
