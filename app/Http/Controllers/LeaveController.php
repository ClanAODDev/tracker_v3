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
            ->with('leaveOfAbsence', 'rank')->get();

        $expiredLeave = (bool) count($membersWithLeave->filter(function ($member) {
            return $member->leaveOfAbsence->expired;
        }));

        return view('leave.index', compact(
            'division', 'membersWithLeave', 'expiredLeave'
        ));
    }

    public function delete(Member $member, Leave $leave)
    {
        $this->authorize('update', $member);

    }

    public function update(Request $request, Member $member)
    {
        $this->authorize('update', $member);

        $leave = Leave::findOrFail($request->leave_id);

        if ($request->approve_leave) {
            $leave->approver()->associate(auth()->user());
        }

        $leave->update($request->all());

        $this->showToast('Leave of absence updated!');

        return redirect(route('leave.index', [$member->primaryDivision->abbreviation]));

    }

    public function edit(Member $member, Leave $leave)
    {
        $this->authorize('update', $member);

        $leave->load('note', 'approver', 'requester');
        $division = $member->primaryDivision;

        return view('leave.edit', compact('division', 'member', 'leave'));
    }

    public function store(CreateLeave $form, Division $division)
    {
        if ($form->member_id && ! $this->isMemberOfDivision($division, $form)) {
            return redirect()->back()
                ->withErrors(['member_id' => "Member {$form->member_id} not assigned to this division!"])
                ->withInput();
        }

        $form->persist();

        $this->showToast('Leave of absence created!');

        return redirect(route('leave.index', $division->abbreviation));
    }

    public function isMemberOfDivision(Division $division, $request)
    {
        $member = Member::whereClanId($request->member_id)->first();

        return $member->primaryDivision instanceof Division &&
            $member->primaryDivision->id === $division->id;
    }
}
