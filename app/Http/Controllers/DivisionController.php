<?php

namespace App\Http\Controllers;

use Charts;
use App\Division;
use Illuminate\Http\Request;
use Whossun\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Session;
use App\Repositories\DivisionRepository;

class DivisionController extends Controller
{
    /**
     * Create a new controller instance.
     * @param DivisionRepository $division
     */
    public function __construct(DivisionRepository $division)
    {
        $this->division = $division;

        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Division $division
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function show(Division $division)
    {
        $censusCounts = $this->division->censusCounts($division);
        $previousCensus = $censusCounts->first();
        $lastYearCensus = $censusCounts->reverse();

        $divisionLeaders = $division->leaders()->with('rank', 'position')->get();
        $platoons = $division->platoons()->with('leader.rank', 'leader.position', 'members')
            ->orderBy('order')->get();

        $generalSergeants = $division->generalSergeants()->with('rank', 'position')->get();
        $staffSergeants = $division->staffSergeants()->with('rank', 'position')->get();

        return view('division.show', compact(
            'division', 'previousCensus', 'platoons', 'lastYearCensus',
            'divisionLeaders', 'generalSergeants', 'staffSergeants'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Division $division
     * @return \Illuminate\Http\Response
     */
    public function edit(Division $division)
    {
        $this->authorize('update', $division);

        $leaders = $division->leaders()->with('rank', 'position', 'user.role', 'user')->get();

        return view('division.modify', compact('division', 'leaders'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Division $division
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Division $division)
    {
        // @FIXME: Move to Form Request
        $this->authorize('update', $division);

        $division->settings()->merge($request->all());

        Toastr::success('Changes saved successfully!', "Update {$division->name}", [
            'positionClass' => 'toast-top-right',
            'progressBar' => true
        ]);

        return back();
    }

    public function partTime(Division $division)
    {
        $partTime = $division->partTimeMembers;

        return view('division.part_time', compact('division', 'partTime'));
    }

    public function rankDemographic(Division $division)
    {
        return $this->division->getRankDemographic($division);
    }

    private function getRanksChart($division)
    {
        $data = $this->rankDemographic($division);

        return Charts::create('area', 'morris')
            ->labels($data['labels'])
            ->values($data['values'])
            ->elementLabel('Rank count')
            ->responsive(true);
    }

    private function getActivityChart($division)
    {
        $data = $this->division->getDivisionActivity($division);

        return Charts::create('donut', 'morris')
            ->labels($data['labels'])
            ->values($data['values'])
            ->colors($data['colors'])
            ->responsive(true);
    }

    public function statistics(Division $division)
    {
        $rankDemographic = $this->getRanksChart($division);

        $activity = $this->getActivityChart($division);

        return view(
            'division.statistics',
            compact('division', 'rankDemographic', 'activity')
        );
    }
}
