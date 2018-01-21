<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Division;
use App\Handle;
use App\Repositories\ClanRepository;
use App\Tag;
use App\User;
use Carbon\Carbon;

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
            'divisions', 'users', 'handles'
        ));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function outstandingMembersReport()
    {
        $clanMax = config('app.aod.maximum_days_inactive');

        $divisions = Division::active()->orderBy('name')->withCount('members')->get();

        $divisions->map(function ($division) use ($clanMax) {
            $divisionMax = $division->settings()->get('inactivity_days');

            $members = $division->members()->whereDoesntHave('leave')->get();

            $outstandingCount = $members
                ->where('last_activity', '<', Carbon::now()->subDays($clanMax)->format('Y-m-d'))
                ->count();

            $inactiveCount = $members
                ->where('last_activity', '<', Carbon::now()->subDays($divisionMax)->format('Y-m-d'))
                ->count();

            $division->outstanding_members = $outstandingCount;
            $division->inactive_members = $inactiveCount - $outstandingCount;
            $division->percent_inactive = number_format(($inactiveCount - $outstandingCount) / $division->members_count * 100,
                1);

            return $division;
        });

        return view('admin.reports.outstanding-members', compact('divisions'));
    }


}
