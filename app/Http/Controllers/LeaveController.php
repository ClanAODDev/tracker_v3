<?php

namespace App\Http\Controllers;

use App\Division;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Division $division)
    {
        $membersWithLeave = $division->members()->whereHas('leaveOfAbsence')
            ->with('activeLeave', 'rank')->get();

        $membersWithLeave = $membersWithLeave->each(function ($member) {
            $member->leaveOfAbsence->expired = Carbon::today() > $member->leaveOfAbsence->end_date->format('Y-m-d');
        });

        return view('division.leaves', compact('division', 'membersWithLeave'));
    }
}
