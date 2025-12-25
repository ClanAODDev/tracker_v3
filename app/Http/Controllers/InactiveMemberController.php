<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteMember;
use App\Models\Activity;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class InactiveMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Division $division)
    {
        $inactivityDays = $division->settings()->inactivity_days;
        $inactivityThreshold = now()->today()->subDays($inactivityDays);

        $inactiveDiscordMembers = $division->members()
            ->where(function ($query) use ($inactivityThreshold) {
                $query->where('last_voice_activity', '<', $inactivityThreshold)
                    ->orWhereNull('last_voice_activity');
            })
            ->where('flagged_for_inactivity', false)
            ->whereDoesntHave('leave', function ($query) {
                $query->whereDate('end_date', '>', today());
            })
            ->with(['squad', 'platoon'])
            ->orderBy('last_voice_activity')
            ->get();

        $allInactiveMembers = $inactiveDiscordMembers;

        if (request()->platoon) {
            $inactiveDiscordMembers = $inactiveDiscordMembers->where('platoon_id', request()->platoon->id);
        }

        $flagActivity = Activity::where('division_id', $division->id)
            ->whereIn('name', ['flagged_member', 'unflagged_member', 'removed_member'])
            ->orderByDesc('created_at')
            ->with(['subject'])
            ->take(20)
            ->get();

        $flaggedMembers = $division->members()->whereFlaggedForInactivity(true)->get();

        $requestPath = 'division.' . explode('/', request()->path())[2];

        $stats = [
            'total' => $allInactiveMembers->count(),
            'flagged' => $flaggedMembers->count(),
            'byPlatoon' => $allInactiveMembers->groupBy('platoon_id')->map->count(),
            'severe' => $allInactiveMembers->filter(fn ($m) => $m->last_voice_activity === null || $m->last_voice_activity < now()->subDays($inactivityDays * 2))->count(),
        ];

        return view('division.inactive-members', compact(
            'division',
            'inactiveDiscordMembers',
            'flaggedMembers',
            'flagActivity',
            'requestPath',
            'stats',
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
