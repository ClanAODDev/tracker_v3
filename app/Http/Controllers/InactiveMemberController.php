<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteMember;
use App\Member;
use Carbon\Carbon;

class InactiveMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($division)
    {
        $inactiveMembers = $division->members()
            ->whereFlaggedForInactivity(false)
            ->where('last_activity', '<', Carbon::today()->subDays(
                $division->settings()->inactivity_days
            ))
            ->whereDoesntHave('leave')
            ->with('rank')
            ->orderBy('last_activity')
            ->get();

        if (request()->platoon) {
            $inactiveMembers = $inactiveMembers->where('platoon_id', request()->platoon->id);
        }

        $flaggedMembers = $division->members()->whereFlaggedForInactivity(true)->get();

        return view('division.inactive-members', compact('division', 'inactiveMembers', 'flaggedMembers'));
    }

    /**
     * Flags a member for inactivity
     *
     * @param Member $member
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function create(Member $member)
    {
        $this->authorize('update', $member);

        $member->flagged_for_inactivity = true;
        $member->save();

        $member->recordActivity('flagged');

        $this->showToast($member->name . " successfully flagged for removal");

        return redirect(route('division.inactive-members', $member->division->abbreviation));
    }

    /**
     * Remove a flag from an inactive member
     *
     * @param Member $member
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Member $member)
    {
        $this->authorize('update', $member);

        $member->flagged_for_inactivity = false;
        $member->save();

        $member->recordActivity('unflagged');

        $this->showToast($member->name . " successfully unflagged");

        return redirect(route('division.inactive-members', $member->division->abbreviation));
    }

    public function removeMember(Member $member, DeleteMember $form)
    {
        $this->authorize('delete', $member);

        $division = $member->division;

        $form->persist();

        $this->showToast(
            ucwords($member->name) . " has been removed from the {$division->name} Division!"
        );

        $member->recordActivity('removed');

        return redirect(route('division.inactive-members', [
                $division->abbreviation
            ]) . '#flagged');
    }
}
