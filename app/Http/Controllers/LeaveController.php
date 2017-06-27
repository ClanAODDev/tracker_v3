<?php

namespace App\Http\Controllers;

use App\Division;
use App\Http\Requests\CreateLeave;
use App\Leave;
use App\Member;
use Illuminate\Http\Request;

/**
 * Class LeaveController
 *
 * @package App\Http\Controllers
 */
class LeaveController extends Controller
{
    /**
     * LeaveController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Division $division)
    {
        $membersWithLeave = $division->members()->whereHas('leaveOfAbsence')
            ->with('activeLeave', 'rank')->get();

        $expiredLeave = (bool) count($membersWithLeave->filter(function ($member) {
            return $member->leaveOfAbsence->expired;
        }));

        return view('division.leaves', compact(
            'division', 'membersWithLeave', 'expiredLeave'
        ));
    }

    public function update(Request $request, Member $member)
    {
        $leave = Leave::findOrFail($request->leave_id);

        if ($request->approve_leave) {
            $leave->approver()->associate(auth()->user());
        }

        $leave->update($request->all());

        $this->showToast('Leave of absence updated!');

        return redirect(route('division.leaves', [$member->primaryDivision->abbreviation]));

    }

    public function edit(Member $member, Leave $leave)
    {
        $leave->load('note', 'approver', 'requester');
        $division = $member->primaryDivision;

        return view('member.edit-leave', compact('division', 'member', 'leave'));
    }

    public function store(CreateLeave $form, Division $division)
    {
        $form->persist();

        $this->showToast('Leave of absence created!');

        return redirect(route('division', $division->abbreviation));
    }
}
