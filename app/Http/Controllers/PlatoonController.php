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

    /**
     * Sort platoon members by position desc, rank asc
     *
     * @param Platoon $platoon
     * @return static
     */
    private function sortPlatoonMembers(Platoon $platoon)
    {
        return $platoon->members
            ->sortBy(['position_id' => 'desc', 'rank_id' => 'asc']);
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
     * Platoon activity endpoint
     *
     * @param Platoon $platoon
     * @param Request $request
     * @return mixed
     */
    public function activity(Platoon $platoon, Request $request)
    {
        if ($request->ajax()) {
            return $this->activityGraphData($platoon);
        }

        return redirect(404);
    }

    /**
     * Generates JSON for platoon activity
     *
     * @param Platoon $platoon
     * @return mixed
     */
    private function activityGraphData(Platoon $platoon)
    {
        $twoWeeks = $platoon->members()->whereRaw('last_forum_login BETWEEN DATE_ADD(CURDATE(), INTERVAL -14 DAY) AND CURDATE()')->count();
        $oneMonth = $platoon->members()->whereRaw('last_forum_login BETWEEN DATE_ADD(CURDATE(), INTERVAL -30 DAY) AND DATE_ADD(CURDATE(), INTERVAL -14 DAY)')->count();
        $moreThanOneMonth = $platoon->members()->whereRaw('last_forum_login < DATE_ADD(CURDATE(), INTERVAL -30 DAY)')->count();

        $data = [
            [
                'label' => '< 2 weeks ago',
                'color' => '#28b62c',
                'highlight' => '#5bc75e',
                'value' => $twoWeeks,
            ],
            [
                'label' => '14 - 30 days ago',
                'color' => '#ff851b',
                'highlight' => '#ffa14f',
                'value' => $oneMonth,
            ],
            [
                'label' => '> 30 days ago',
                'color' => '#ff4136',
                'highlight' => '#ff6c64',
                'value' => $moreThanOneMonth,
            ],
        ];

        return json_encode($data);
    }
}
