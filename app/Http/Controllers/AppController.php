<?php

namespace App\Http\Controllers;

use App\Division;

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
        $divisions = Division::active()->orderBy('name')->get();

        return view('layouts.home')->with(
            compact('divisions')
        );
    }
}
