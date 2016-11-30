<?php

namespace App\Http\Controllers;

use App\Division;
use App\Platoon;
use App\Squad;
use Illuminate\Http\Request;

use App\Http\Requests;

class SquadController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Http\Response
     */
    public function index(Division $division, Platoon $platoon)
    {
        $squads = $platoon->squads()
            ->with('members.rank', 'members.position')
            ->get();

        return view('platoon.squads', compact(
            'platoon', 'division', 'squads'
        ));
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Squad $squad
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function show(Squad $squad)
    {
        return view('squad.show', compact('squad'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Squad $squad
     * @return \Illuminate\Http\Response
     */
    public function edit(Squad $squad)
    {
        $this->authorize('update', $squad);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Squad $squad)
    {
        $this->authorize('update', $squad);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Squad $squad
     * @return \Illuminate\Http\Response
     */
    public function destroy(Squad $squad)
    {
        $this->authorize('delete', $squad);
    }
}
