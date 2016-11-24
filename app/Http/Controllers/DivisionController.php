<?php

namespace App\Http\Controllers;

use App\Division;
use Illuminate\Http\Request;
use ConsoleTVs\Charts\Charts;
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
        $chart = $this->getRanksChart($division);

        return view('division.show', compact('division', 'chart'));
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
        $this->authorize('update', $division);

        $division->settings()->merge($request->all());

        return redirect()->action('DivisionController@edit', $division->abbreviation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Division $division
     * @return \Illuminate\Http\Response
     */
    public function destroy(Division $division)
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @param Division $division
     * @return \Illuminate\Http\Response
     */
    public function squads(Division $division)
    {
        return view('division.squads', compact('division'));
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

    public function statistics(Division $division)
    {
        $rankDemographic = $this->getRanksChart($division);

        return view('division.statistics', compact('division', 'rankDemographic'));
    }
}
