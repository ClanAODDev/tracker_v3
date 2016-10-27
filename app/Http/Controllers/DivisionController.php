<?php

namespace App\Http\Controllers;

use DB;
use App\Division;
use App\Http\Requests;
use Illuminate\Http\Request;
use ConsoleTVs\Charts\Charts;
use App\Repositories\DivisionRepository;

class DivisionController extends Controller
{
    /**
     * Create a new controller instance.
     *
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

        return Charts::create('bar', 'morris')
            ->setTitle('Rank Demographic')
            ->setLabels($data['labels'])
            ->setValues($data['values'])
            ->setElementLabel('Rank count')
            ->setResponsive(true);
    }
}
