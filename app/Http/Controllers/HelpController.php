<?php

namespace App\Http\Controllers;

/**
 * Class HelpController
 *
 * @package App\Http\Controllers
 */
class HelpController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('help.index');
    }

    public function divisionStructures()
    {
        return view('help.division-structures');
    }
}
