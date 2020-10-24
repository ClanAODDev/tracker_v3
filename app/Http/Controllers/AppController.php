<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Auth;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
class AppController extends \App\Http\Controllers\Controller
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
     * @return Factory|View
     */
    public function index()
    {
        $myDivision = \Auth::user()->member->division;
        $maxDays = config('app.aod.maximum_days_inactive');
        $myDivision->outstandingInactives = $myDivision->members()->whereDoesntHave('leave')->where('last_activity', '<', \Carbon\Carbon::now()->subDays($maxDays)->format('Y-m-d'))->count();
        $activeDivisions = \App\Models\Division::active()->withCount('members')->orderBy('name')->get();
        $divisions = $activeDivisions->except($myDivision->id);
        return view('home.show', compact('divisions', 'myDivision'));
    }
}
