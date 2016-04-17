<?php

namespace App\Http\Controllers;

use App\Division;
use App\Http\Requests;
use App\Platoon;
use Illuminate\Http\Request;

class PlatoonController extends Controller
{
    /**
     * PlatoonController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
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
     * @param Platoon $platoon
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function show(Platoon $platoon)
    {
        $platoon->members = $this->sortPlatoonMembers($platoon);

        return view('platoon.show', compact('platoon'));
    }

    private function sortPlatoonMembers(Platoon $platoon)
    {
        return $platoon->members
            ->sortBy(['position_id' => 'desc', 'rank_id' => 'asc']);
    }

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

    public function activity(Platoon $platoon, Request $request)
    {
        if ($request->ajax())
            return $platoon->forumActivity;

        return redirect(404);
    }
}
