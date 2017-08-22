<?php

namespace App\Http\Controllers;

use App\AOD\MemberSync\SyncMemberData;
use App\Division;
use Auth;
use Mail;

class AppController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function changeLog()
    {
        return view('application.changelog');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $myDivision = Auth::user()->member->division;

        $activeDivisions = Division::active()->withCount('members')->orderBy('name')->get();
        $divisions = $activeDivisions->except($myDivision->id);

        return view('home.show', compact(
            'divisions',
            'myDivision'
        ));
    }
}
