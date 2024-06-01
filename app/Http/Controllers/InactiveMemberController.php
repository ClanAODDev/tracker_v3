<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteMember;
use App\Models\Activity;
use App\Models\Member;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

class InactiveMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return Factory|View
     */
    public function index($division)
    {
        $inactiveTSMembers = $this->getInactiveMembers($division, 'last_ts_activity');
        $inactiveDiscordMembers = $this->getInactiveMembers($division, 'last_voice_activity');

        if (request()->platoon) {
            $inactiveTSMembers = $inactiveTSMembers->where('platoon_id', request()->platoon->id);
            $inactiveDiscordMembers = $inactiveDiscordMembers->where('platoon_id', request()->platoon->id);
        }

        $flagActivity = Activity::whereDivisionId($division->id)->where(function ($query) {
            $query->where('name', 'flagged_member')->orWhere('name', 'unflagged_member')->orWhere('name',
                'removed_member');
        })->orderByDesc('created_at')->with('subject', 'subject.rank')->get();

        $flaggedMembers = $division->members()->with('rank')->whereFlaggedForInactivity(true)->get();

        /**
         * Using this to determine the active route, whether filtering
         * by teamspeak or forum. Used in platoon filter options, reset
         * filter button.
         */
        $requestPath = 'division.' . explode('/', request()->path())[2];

        return view('division.inactive-members', compact(
            'division',
            'inactiveTSMembers',
            'inactiveDiscordMembers',
            'flaggedMembers',
            'flagActivity',
            'requestPath',
        ));
    }

    private function getInactiveMembers($division, $activityColumn)
    {
        return $division->members()->whereFlaggedForInactivity(false)
            ->where($activityColumn, '<', now()->today()->subDays($division->settings()->inactivity_days))
            ->whereDoesntHave('leave')
            ->with('rank', 'squad')
            ->orderBy($activityColumn)
            ->get();
    }

    /**
     * Flags a member for inactivity.
     *
     * @return Redirector|RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function create(Member $member)
    {
        $this->authorize('update', $member);
        $member->flagged_for_inactivity = true;
        $member->save();
        $member->recordActivity('flagged');
        $this->showSuccessToast($member->name . ' successfully flagged for removal');

        return redirect()->back();
    }

    /**
     * Remove a flag from an inactive member.
     *
     * @return Redirector|RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function destroy(Member $member)
    {
        $this->authorize('update', $member);
        $member->flagged_for_inactivity = false;
        $member->save();
        $member->recordActivity('unflagged');
        $this->showSuccessToast($member->name . ' successfully unflagged');

        return redirect(route('division.inactive-members', $member->division->slug));
    }

    public function removeMember(Member $member, DeleteMember $form)
    {
        $this->authorize('delete', $member);
        $division = $member->division;
        $form->persist();
        $this->showSuccessToast(ucwords($member->name) . " has been removed from the {$division->name} Division!");
        $member->recordActivity('removed');

        return redirect(route('division.inactive-members', [$division->slug]) . '#flagged');
    }
}
