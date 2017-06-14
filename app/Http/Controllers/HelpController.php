<?php

namespace App\Http\Controllers;

/**
 * Class HelpController
 *
 * @package App\Http\Controllers
 */
class HelpController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('help.index');
    }
}
