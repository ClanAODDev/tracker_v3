<?php

namespace App\Http\Controllers;

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
        $divisions = Division::active()->orderBy('name')->get();

        Toastr::info('Messages in here', 'Title');

        return view('layouts.home', compact('divisions'));
    }
}
