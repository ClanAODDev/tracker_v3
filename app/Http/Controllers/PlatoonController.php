<?php

namespace App\Http\Controllers;

use App\Division;
use App\Http\Requests\CreatePlatoonForm;
use App\Member;
use App\Platoon;
use App\Repositories\PlatoonRepository;
use App\User;
use ConsoleTVs\Charts\Charts;
use Illuminate\Http\Request;

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
     * @param User $user
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
                ->withErrors(['leader' => 'Member not assigned to this division!'])
                ->withInput();
        }

        $form->persist();

        flash("{$division->locality('platoon')} has been created! If you assigned a leader, you will need to ensure you have also updated their account access to 'Senior Leader'",
            'success');

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
     * @param CreatePlatoonForm $request
     * @param Division $division
     * @return bool
     */
    public function isMemberOfDivision(Division $division, CreatePlatoonForm $request)
    {
        $member = Member::whereClanId($request->leader)->first();

        return $member->primaryDivision instanceOf Division &&
            $member->primaryDivision->id === $division->id;
    }

}
