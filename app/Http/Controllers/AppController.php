<?php

namespace App\Http\Controllers;

use Auth;
use Mail;
use App\Division;
use App\Mail\WelcomeEmail;
use Whossun\Toastr\Facades\Toastr;
use App\Repositories\ClanRepository;

class AppController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(ClanRepository $clanRepository)
    {
        $this->clan = $clanRepository;

        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $myDivision = Auth::user()->member->primaryDivision;
        $memberCount = $this->clan->totalActiveMembers();
        $divisions = Division::active()->withCount('members')->orderBy('name')->get();

        $previousCensus = $this->clan->censusCounts(1);
        $lastYearCensus = $this->clan->censusCounts(8)->reverse();

        return view('layouts.home', compact(
            'divisions', 'myDivision', 'memberCount',
            'previousCensus', 'lastYearCensus'
        ));
    }
}

/* Toastr::success('You have successfully logged in!', 'Hello, ' . strtoupper(Auth::user()->name), [
        'positionClass' => 'toast-top-right',
        'progressBar' => true
    ]);*/
