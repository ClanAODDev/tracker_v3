<?php

namespace App\Http\Controllers;

use App\Activity;
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
        $activityLog = Activity::with([
            'subject' => function ($query) {
                // provide context even if a subject is "trashed"
                $query->withTrashed();
            }
        ])->get();

        return view('admin.index', compact(
            'divisions', 'users', 'handles', 'activityLog'
        ));
    }


}
