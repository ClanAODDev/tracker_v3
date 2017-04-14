<?php

namespace App\Http\Controllers;

class DeveloperController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        return view('developer.index');
    }
}
