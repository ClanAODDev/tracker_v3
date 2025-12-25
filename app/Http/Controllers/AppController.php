<?php

namespace App\Http\Controllers;

use App\Data\DivisionLeaderboardData;
use App\Data\PendingActionsData;
use App\Models\Division;
use Auth;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class AppController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function changeLog()
    {
        return view('application.changelog');
    }

    public function index(): Factory|View
    {
        $user = Auth::user();
        $myDivision = $user->member->division;

        $pendingActions = PendingActionsData::forDivision($myDivision, $user);
        $leaderboard = DivisionLeaderboardData::forUser($user);

        $divisions = Division::active()->withoutFloaters()->withCount('members')
            ->orderBy('name')
            ->get()
            ->except($myDivision->id);

        return view('home.show', compact('divisions', 'myDivision', 'pendingActions', 'leaderboard'));
    }
}
