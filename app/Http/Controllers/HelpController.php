<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

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
     * @return Factory|View
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
