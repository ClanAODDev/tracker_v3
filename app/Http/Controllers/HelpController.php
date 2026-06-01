<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

/**
 * Class HelpController.
 */
#[Middleware('auth')]
class HelpController extends Controller
{
    /**
     * @return Factory|View
     */
    public function index()
    {
        return view('help.index');
    }
}
