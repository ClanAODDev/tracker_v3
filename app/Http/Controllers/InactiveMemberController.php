<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Http\Requests\DeleteMember;
use App\Models\Activity;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class InactiveMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Division $division): View
    {
        $inactivityDays = $division->settings()->inactivity_days;

        $inactiveDiscordMembers = $this->getInactiveMembers($division, $inactivityDays);
        $allInactiveMembers = $inactiveDiscordMembers;

        if (request()->platoon) {
            $inactiveDiscordMembers = $inactiveDiscordMembers->where('platoon_id', request()->platoon->id);
        }

        $flaggedMembers = $division->members()
            ->whereFlaggedForInactivity(true)
            ->with(['squad', 'platoon'])
            ->get();

        return view('division.inactive-members', [
            'division' => $division,
            'inactiveDiscordMembers' => $inactiveDiscordMembers,
            'flaggedMembers' => $flaggedMembers,
            'flagActivity' => $this->getRecentFlagActivity($division),
            'requestPath' => 'division.' . explode('/', request()->path())[2],
            'stats' => $this->buildStats($allInactiveMembers, $flaggedMembers, $inactivityDays),
        ]);
    }

    public function create(Member $member): RedirectResponse
    {
        $this->authorize('flag-inactive', $member);
        $this->setFlagStatus($member, true);
        $this->showSuccessToast($member->name . ' successfully flagged for removal');

        return redirect()->back();
    }

    public function destroy(Member $member): RedirectResponse
    {
        $this->authorize('flag-inactive', $member);
        $this->setFlagStatus($member, false);
        $this->showSuccessToast($member->name . ' successfully unflagged');

        return redirect(route('division.inactive-members', $member->division->slug));
    }

    public function removeMember(Member $member, DeleteMember $form): RedirectResponse
    {
        $this->authorize('separate', $member);
        $division = $member->division;
        $form->persist();
        $this->showSuccessToast(ucwords($member->name) . " has been removed from the {$division->name} Division!");
        $member->recordActivity(ActivityType::REMOVED);

        return redirect(route('division.inactive-members', [$division->slug]) . '#flagged');
    }

    public function bulkFlag(Request $request, Division $division): JsonResponse
    {
        return $this->bulkUpdateFlag($request, $division, true);
    }

    public function bulkUnflag(Request $request, Division $division): JsonResponse
    {
        return $this->bulkUpdateFlag($request, $division, false);
    }

    private function getInactiveMembers(Division $division, int $inactivityDays): Collection
    {
        $threshold = now()->subDays($inactivityDays);

        return $division->members()
            ->where(function ($query) use ($threshold) {
                $query->where('last_voice_activity', '<', $threshold)
                    ->orWhereNull('last_voice_activity');
            })
            ->where('flagged_for_inactivity', false)
            ->whereDoesntHave('leave', fn ($q) => $q->whereDate('end_date', '>', today()))
            ->with(['squad', 'platoon'])
            ->orderBy('last_voice_activity')
            ->get();
    }

    private function getRecentFlagActivity(Division $division): Collection
    {
        return Activity::where('division_id', $division->id)
            ->whereIn('name', ['removed_member'])
            ->orderByDesc('created_at')
            ->with(['subject'])
            ->take(20)
            ->get();
    }

    private function buildStats(Collection $inactive, Collection $flagged, int $inactivityDays): array
    {
        $severeThreshold = now()->subDays($inactivityDays * 2);

        return [
            'total' => $inactive->count(),
            'flagged' => $flagged->count(),
            'byPlatoon' => $inactive->groupBy('platoon_id')->map->count(),
            'severe' => $inactive->filter(
                fn ($m) => $m->last_voice_activity === null || $m->last_voice_activity < $severeThreshold
            )->count(),
        ];
    }

    private function setFlagStatus(Member $member, bool $flagged): void
    {
        $member->flagged_for_inactivity = $flagged;
        $member->save();
    }

    private function bulkUpdateFlag(Request $request, Division $division, bool $flag): JsonResponse
    {
        $this->authorize('flag-inactive', Member::class);

        $validated = $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer',
        ]);

        $members = Member::whereIn('clan_id', $validated['member_ids'])
            ->where('division_id', $division->id)
            ->where('flagged_for_inactivity', ! $flag)
            ->get();

        $updatedIds = [];
        foreach ($members as $member) {
            $this->setFlagStatus($member, $flag);
            $updatedIds[] = $member->clan_id;
        }

        $action = $flag ? 'flagged for removal' : 'unflagged';
        $count = count($updatedIds);

        return response()->json([
            'success' => true,
            'count' => $count,
            $flag ? 'flaggedIds' : 'unflaggedIds' => $updatedIds,
            'message' => $count . ' member' . ($count !== 1 ? 's' : '') . ' ' . $action,
        ]);
    }
}
