<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteMember;
use App\Models\Activity;
use App\Models\Division;
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
    public function index(Division $division)
    {
        $inactiveDiscordMembers = $division->members()
            ->where(function ($query) use ($division) {
                $inactivityThreshold = now()->today()->subDays($division->settings()->inactivity_days);
                $query->where('last_voice_activity', '<', $inactivityThreshold)
                    ->orWhereNull('last_voice_activity');
            })
            ->where('flagged_for_inactivity', false)
            ->whereDoesntHave('leave', function ($query) {
                $query->whereDate('end_date', '>', today());
            })
            ->with('squad')
            ->orderBy('last_voice_activity')
            ->get();

        if (request()->platoon) {
            $inactiveDiscordMembers = $inactiveDiscordMembers->where('platoon_id', request()->platoon->id);
        }

        $flagActivity = Activity::where('division_id', $division->id)
            ->whereIn('name', ['flagged_member', 'unflagged_member', 'removed_member'])
            ->orderByDesc('created_at')
            ->with(['subject'])
            ->get();

        $flaggedMembers = $division->members()->whereFlaggedForInactivity(true)->get();

        /**
         * Using this to determine the active route, whether filtering
         * by teamspeak or forum. Used in platoon filter options, reset
         * filter button.
         */
        $requestPath = 'division.' . explode('/', request()->path())[2];

        return view('division.inactive-members', compact(
            'division',
            'inactiveDiscordMembers',
            'flaggedMembers',
            'flagActivity',
            'requestPath',
        ));
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
        $this->authorize('flag-inactive', $member);
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
        $this->authorize('flag-inactive', $member);
        $member->flagged_for_inactivity = false;
        $member->save();
        $member->recordActivity('unflagged');
        $this->showSuccessToast($member->name . ' successfully unflagged');

        return redirect(route('division.inactive-members', $member->division->slug));
    }

    public function removeMember(Member $member, DeleteMember $form)
    {
        $this->authorize('separate', $member);
        $division = $member->division;
        $form->persist();
        $this->showSuccessToast(ucwords($member->name) . " has been removed from the {$division->name} Division!");
        $member->recordActivity('removed');

        return redirect(route('division.inactive-members', [$division->slug]) . '#flagged');
    }
}
