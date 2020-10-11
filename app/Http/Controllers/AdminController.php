<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Handle;
use App\Repositories\ClanRepository;
use App\Models\User;

class AdminController extends Controller
{
    public function __construct(ClanRepository $clanRepository)
    {
        $this->clanRepository = $clanRepository;

        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $divisions = Division::orderBy('name')->get();
        $handles = Handle::withCount('divisions')->get();
        $users = User::with('role', 'member.rank', 'member')->get();

        return view('admin.index', compact(
            'divisions',
            'users',
            'handles'
        ));
    }
}
