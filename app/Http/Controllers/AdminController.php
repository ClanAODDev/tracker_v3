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
        $divisions = Division::orderBy('name')->get();
        $handles = Handle::withCount('divisions')->get();
        $defaultTags = Tag::where('default', true)->get();
        $allTags = Tag::with('notes', 'division')->orderBy('division_id')->get();
        $users = User::with('role', 'member.rank', 'member')->get();


        return view('admin.index', compact(
            'divisions', 'users', 'handles', 'allTags', 'defaultTags'
        ));
    }


}
