<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLeave;
use App\Http\Requests\UpdateLeave;
use App\Models\Division;
use App\Models\Leave;
use App\Models\Member;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

/**
 * Class LeaveController.
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
     * @return Factory|View
     */
    public function index(Division $division)
    {
        $membersWithLeave = $division->members()->whereHas('leave')
            ->with('leave', 'rank')->get();

        $expiredLeave = (bool) \count($membersWithLeave->filter(fn ($member) => $member->leave->expired));

        return view('leave.index', compact(
            'division',
            'membersWithLeave',
            'expiredLeave'
        ));
    }

    /**
     * @return Redirector|RedirectResponse
     *
     * @throws Exception
     * @throws AuthorizationException
     */
    public function delete(Member $member, Leave $leave)
    {
        $this->authorize('update', $member);

        $leave->delete();

        $this->showToast('Leave successfully deleted!');

        return redirect(route('leave.index', $member->division->abbreviation));
    }

    /**
     * @return Redirector|RedirectResponse
     */
    public function update(UpdateLeave $form, Member $member)
    {
        if ($form->approve_leave && ($form->requester_id === auth()->user()->id)) {
            return redirect()->back()
                ->withErrors(['member_id' => 'You cannot approve an LOA that you requested'])
                ->withInput();
        }

        $form->persist();

        $this->showToast('Leave of absence updated!');

        return redirect(route('leave.index', [$member->division->abbreviation]));
    }

    /**
     * @return Factory|View
     */
    public function edit(Member $member, Leave $leave)
    {
        $this->authorize('updateLeave', $member);

        $leave->load('note', 'approver', 'requester');
        $division = $member->division;

        return view('leave.edit', compact('division', 'member', 'leave'));
    }

    /**
     * @return LeaveController|RedirectResponse
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
     * @return bool
     */
    public function isMemberOfDivision(Division $division, $request)
    {
        $member = Member::whereClanId($request->member_id)->first();

        return $member->division instanceof Division
            && $member->division->id === $division->id;
    }
}
