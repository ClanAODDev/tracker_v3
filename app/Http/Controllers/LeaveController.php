<?php

namespace App\Http\Controllers;

use App\Division;
use App\Http\Requests\CreateLeave;
use App\Http\Requests\UpdateLeave;
use App\Leave;
use App\Member;

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
        $membersWithLeave = $division->members()->whereHas('leave')
            ->with('leave', 'rank')->get();

        $expiredLeave = (bool) count($membersWithLeave->filter(function ($member) {
            return $member->leave->expired;
        }));

        return view('leave.index', compact(
            'division',
            'membersWithLeave',
            'expiredLeave'
        ));
    }

    /**
     * @param Member $member
     * @param Leave $leave
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Member $member, Leave $leave)
    {
        $this->authorize('update', $member);

        $leave->delete();

        $this->showToast('Leave successfully deleted!');

        return redirect(route('leave.index', $member->division->abbreviation));
    }

    /**
     * @param UpdateLeave $form
     * @param Member $member
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(UpdateLeave $form, Member $member)
    {
        if ($form->approve_leave && ($form->requester_id == auth()->user()->id)) {
            return redirect()->back()
                ->withErrors(['member_id' => "You cannot approve an LOA that you requested"])
                ->withInput();
        }

        $form->persist();

        $this->showToast('Leave of absence updated!');

        return redirect(route('leave.index', [$member->division->abbreviation]));
    }

    /**
     * @param Member $member
     * @param Leave $leave
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Member $member, Leave $leave)
    {
        $this->authorize('update', $member);

        $leave->load('note', 'approver', 'requester');
        $division = $member->division;

        return view('leave.edit', compact('division', 'member', 'leave'));
    }

    /**
     * @param CreateLeave $form
     * @param Division $division
     * @return LeaveController|\Illuminate\Http\RedirectResponse
     */
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

    /**
     * @param Division $division
     * @param $request
     * @return bool
     */
    public function isMemberOfDivision(Division $division, $request)
    {
        $member = Member::whereClanId($request->member_id)->first();

        return $member->division instanceof Division &&
            $member->division->id === $division->id;
    }
}
