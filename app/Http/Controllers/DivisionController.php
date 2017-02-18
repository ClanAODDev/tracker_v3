<?php

namespace App\Http\Controllers;

use App\Division;
use Illuminate\Http\Request;
use ConsoleTVs\Charts\Charts;
use App\Repositories\DivisionRepository;
use Illuminate\Support\Facades\Session;
use Whossun\Toastr\Facades\Toastr;

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
        $chart = $this->getRanksChart($division);

        $divisionLeaders = $division->leaders()->with('rank', 'position')->get();
        $platoons = $division->platoons()->with('leader.rank', 'leader.position', 'members')
            ->orderBy('order')->get();

        $generalSergeants = $division->generalSergeants()->with('rank', 'position')->get();
        $staffSergeants = $division->staffSergeants()->with('rank', 'position')->get();

        return view('division.show', compact(
            'division', 'chart', 'platoons', 'divisionLeaders',
            'generalSergeants', 'staffSergeants'
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

        return view('division.modify', compact('division'));
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
            ->setLabels($data['labels'])
            ->setValues($data['values'])
            ->setElementLabel('Rank count')
            ->setResponsive(true);
    }

    private function getActivityChart($division)
    {
        $data = $this->division->getDivisionActivity($division);

        return Charts::create('donut', 'morris')
            ->setLabels($data['labels'])
            ->setValues($data['values'])
            ->setColors($data['colors'])
            ->setResponsive(true);
    }

    public function statistics(Division $division)
    {
        $rankDemographic = $this->getRanksChart($division);

        $activity = $this->getActivityChart($division);

        return view('division.statistics',
            compact('division', 'rankDemographic', 'activity')
        );
    }
}
