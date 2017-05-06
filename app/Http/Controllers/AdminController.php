<?php

namespace App\Http\Controllers;

use App\Division;
use App\Handle;
use App\Repositories\ClanRepository;
use App\User;

class AdminController extends Controller
{
    public function __construct(ClanRepository $clanRepository)
    {
        $this->clanRepository = $clanRepository;

        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $divisions = Division::all();

        $handles = Handle::with('divisions')->get();

        $users = User::with('role', 'member', 'member.rank')->get();

        return view('admin.index', compact('divisions', 'users', 'handles'));
    }


}
