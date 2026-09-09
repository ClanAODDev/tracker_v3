<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Http\Requests\Member\DeleteMember;
use App\Models\Activity;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class InactiveMemberController extends Controller
{
    public function index(Division $division): Response
    {
        $user           = auth()->user();
        $inactivityDays = $division->settings()->inactivity_days;

        $inactiveDiscordMembers = $this->getInactiveMembers($division, $inactivityDays);
        $allInactiveMembers     = $inactiveDiscordMembers;

        if (request()->platoon) {
            $inactiveDiscordMembers = $inactiveDiscordMembers->where('platoon_id', request()->platoon->id)->values();
        }

        $flaggedMembers = $division->members()
            ->whereFlaggedForInactivity(true)
            ->with(['squad', 'platoon', 'leave'])
            ->get();

        return Inertia::render('division/inactive-members', [
            'division' => [
                'name'           => $division->name,
                'slug'           => $division->slug,
                'platoonLabel'   => $division->locality('Platoon'),
                'inactivityDays' => $inactivityDays,
            ],
            'stats'         => $this->buildStats($allInactiveMembers, $flaggedMembers, $inactivityDays),
            'activePlatoon' => request()->platoon?->id,
            'platoons'      => $division->platoons->map(fn ($p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'count' => $allInactiveMembers->where('platoon_id', $p->id)->count(),
            ])->values(),
            'inactive'    => $inactiveDiscordMembers->map(fn ($m) => $this->row($m, $division, $inactivityDays))->values(),
            'flagged'     => $flaggedMembers->map(fn ($m) => $this->row($m, $division, $inactivityDays, flagged: true))->values(),
            'activityLog' => $this->getRecentFlagActivity($division)
                ->filter(fn ($a) => isset($a->subject->name))
                ->map(fn ($a) => [
                    'icon' => $a->name->feedIcon(),
                    'user' => $a->user?->name ?? 'Unknown',
                    'verb' => match ($a->name) {
                        ActivityType::FLAGGED   => 'flagged',
                        ActivityType::UNFLAGGED => 'unflagged',
                        ActivityType::REMOVED   => 'removed',
                        default                 => 'updated',
                    },
                    'subject' => $a->subject->name,
                    'when'    => $a->created_at->diffForHumans(),
                ])->values(),
            'can' => [
                'remind' => $user->can('remindActivity', Member::class),
                'flag'   => $user->can('flag-inactive', Member::class),
            ],
            'bulk' => [
                'pm'       => route('private-message.create', ['division' => $division]),
                'reminder' => route('bulk-reminder.store', $division),
                'flag'     => route('inactive.bulk-flag', $division),
                'unflag'   => route('inactive.bulk-unflag', $division),
            ],
        ]);
    }

    private function row(Member $member, Division $division, int $inactivityDays, bool $flagged = false): array
    {
        $days     = $member->last_voice_activity?->diffInDays(now());
        $severity = $days === null || $days >= $inactivityDays * 2
            ? 'severe'
            : ($days >= $inactivityDays * 1.5 ? 'warning' : 'normal');
        $reminder = $member->last_activity_reminder_at;
        $user     = auth()->user();

        return [
            'id'         => $member->clan_id,
            'name'       => $member->name,
            'rankAbbr'   => $member->rank->getAbbreviation(),
            'profileUrl' => route('member', $member->getUrlParams()),
            'voice'      => [
                'label' => $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks', 'months']),
                'iso'   => $member->last_voice_activity?->toIso8601String(),
            ],
            'reminder' => [
                'date'          => $reminder?->format('n/j/y'),
                'remindedToday' => (bool) $reminder?->isToday(),
                'human'         => $reminder ? 'Reminded ' . $reminder->diffForHumans() : 'Not reminded',
            ],
            'status'     => $member->last_voice_status?->getLabel() ?? 'Unknown',
            'unit'       => trim(($member->platoon->name ?? 'Unassigned') . ($member->squad ? ' / ' . $member->squad->name : '')),
            'severity'   => $severity,
            'forumPmUrl' => doForumFunction([$member->clan_id], 'pm'),
            'flagUrl'    => route('member.flag-inactive', $member->clan_id),
            'unflagUrl'  => $flagged ? route('member.unflag-inactive', $member->clan_id) : null,
            'removeUrl'  => $flagged && $user->can('separate', $member) ? route('member.drop-for-inactivity', $member->clan_id) : null,
            'canRemind'  => $user->can('remindActivity', $member),
            'canFlag'    => $user->can('flag-inactive', $member),
        ];
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

        return redirect(route('division.inactive-members', $member->division->slug) . '#flagged');
    }

    public function removeMember(Member $member, DeleteMember $form): RedirectResponse
    {
        $this->authorize('separate', $member);
        $division = $member->division;
        $member->recordActivity(ActivityType::REMOVED);
        $form->persist();
        $this->showSuccessToast(ucwords($member->name) . " has been removed from the {$division->name} Division!");

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
            ->whereIn('name', [ActivityType::FLAGGED, ActivityType::UNFLAGGED, ActivityType::REMOVED])
            ->orderByDesc('created_at')
            ->with(['subject'])
            ->take(20)
            ->get();
    }

    private function buildStats(Collection $inactive, Collection $flagged, int $inactivityDays): array
    {
        $severeThreshold = now()->subDays($inactivityDays * 2);

        return [
            'total'     => $inactive->count(),
            'flagged'   => $flagged->count(),
            'byPlatoon' => $inactive->groupBy('platoon_id')->map->count(),
            'severe'    => $inactive->filter(
                fn ($m) => $m->last_voice_activity === null || $m->last_voice_activity < $severeThreshold
            )->count(),
        ];
    }

    private function setFlagStatus(Member $member, bool $flagged): void
    {
        $member->flagged_for_inactivity = $flagged;
        $member->save();
        $member->recordActivity($flagged ? ActivityType::FLAGGED : ActivityType::UNFLAGGED);
    }

    private function bulkUpdateFlag(Request $request, Division $division, bool $flag): JsonResponse
    {
        $this->authorize('flag-inactive', Member::class);

        $validated = $request->validate([
            'member_ids'   => 'required|array',
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
        $count  = count($updatedIds);

        return response()->json([
            'success'                             => true,
            'count'                               => $count,
            $flag ? 'flaggedIds' : 'unflaggedIds' => $updatedIds,
            'message'                             => $count . ' member' . ($count !== 1 ? 's' : '') . ' ' . $action,
        ]);
    }
}
