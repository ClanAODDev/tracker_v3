<?php

namespace App\Http\Controllers;

use Auth;
use Mail;
use App\Division;
use App\Mail\WelcomeEmail;
use Whossun\Toastr\Facades\Toastr;

class AppController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
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

        $divisions = Division::active()
            ->withCount('members')
            ->orderBy('name')->get();

       /* Toastr::success('You have successfully logged in!', 'Hello, ' . strtoupper(Auth::user()->name), [
            'positionClass' => 'toast-top-right',
            'progressBar' => true
        ]);*/

        return view('layouts.home', compact('divisions', 'myDivision'));
    }
}
