<?php

namespace App\Http\Controllers;

use App\Division;
use App\Http\Requests;

class AppController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        $divisions = Division::enabled()->get();

        return view('layouts.home')->with(
            compact('divisions')
        );
    }

}
