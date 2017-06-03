<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Division;
use App\Handle;
use App\Repositories\ClanRepository;
use App\Tag;
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
        $handles = Handle::withCount('divisions')->get();
        $defaultTags = Tag::where('default', true)->get();
        $allTags = Tag::with('notes', 'division')->get();
        $users = User::with('role', 'member.rank')->get();
        $activityLog = Activity::with([
            'subject' => function ($query) {
                // provide context even if a subject is "trashed"
                $query->withTrashed();
            }
        ])->orderByDesc('id')->get();

        return view('admin.index', compact(
            'divisions', 'users', 'handles', 'allTags',
            'activityLog', 'defaultTags'
        ));
    }


}
