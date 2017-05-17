<?php

namespace App\Http\Controllers;

use App\Division;
use App\Platoon;
use Illuminate\Http\Request;

/**
 * Class RecruitingController
 *
 * @package App\Http\Controllers
 */
class RecruitingController extends Controller
{

    /**
     * RecruitingController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $divisions = Division::active()->get()->pluck('name', 'abbreviation');

        return view('recruit.index', compact('divisions'));
    }

    /**
     * @param Division $division
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function stepOne(Division $division, Request $request) {
        return view('recruit.step-one', compact('division'));
    }

    public function searchPlatoonForSquads(Request $request)
    {
        return $this->getSquadsFor(Platoon::find($request->platoon));
    }

    /**
     * @param Platoon $platoon
     * @return mixed
     */
    public function getSquadsFor(Platoon $platoon)
    {
        return $platoon->squads;
    }
}
