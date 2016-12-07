<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePlatoonRequest;
use App\Member;
use App\Platoon;
use App\Division;
use App\Http\Requests;
use Illuminate\Http\Request;
use ConsoleTVs\Charts\Charts;
use App\Repositories\PlatoonRepository;

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
        return view('platoon.create', compact('division'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreatePlatoonRequest $request
     * @param Division $division
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreatePlatoonRequest $request, Division $division)
    {
        if ( ! $this->isMemberOfDivision($division, $request)) {
            return redirect()->back()
                ->withErrors(['leader' => 'Member not assigned to this division!'])
                ->withInput();
        }

        $this->createPlatoon($request, $division);

        flash("{$division->locality('platoon')} has been created!", 'success');

        return redirect()->route('division', $division->abbreviation);
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
            'rank', 'position', 'divisions'
        )->get();

        $activityGraph = $this->activityGraphData($platoon);

        return view('platoon.show',
            compact('platoon', 'members', 'division', 'activityGraph')
        );
    }

    /**
     * Get platoon's squads
     *
     * @param Platoon $platoon
     * @return mixed
     */
    public function squads(Platoon $platoon)
    {
        return view('platoon.squads', compact('platoon'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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

    /**
     * Generates data for platoon activity
     *
     * @param Platoon $platoon
     * @return mixed
     */
    private function activityGraphData(Platoon $platoon)
    {
        $data = $this->platoon->getPlatoonActivity($platoon);

        return Charts::create('donut', 'morris')
            ->setLabels($data['labels'])
            ->setValues($data['values'])
            ->setColors($data['colors'])
            ->setResponsive(true);
    }

    /**
     * @param Request $request
     * @param Division $division
     */
    public function createPlatoon(Request $request, Division $division)
    {
        $platoon = new Platoon;
        $platoon->name = $request->name;
        $platoon->leader_id = $request->leader;
        $platoon->division()->associate($division);
        $platoon->save();
    }

    /**
     * @param CreatePlatoonRequest $request
     * @param Division $division
     * @return bool
     */
    public function isMemberOfDivision(Division $division, CreatePlatoonRequest $request)
    {
        if ( ! $request->leader) {

            return true;
        }

        $member = Member::whereClanId($request->leader)->first();

        return $member->primaryDivision instanceOf Division &&
            $member->primaryDivision->id === $division->id;
    }

}
