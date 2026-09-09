<?php

namespace App\Http\Controllers;

use App\Data\UnitStatsData;
use App\Enums\ActivityType;
use App\Enums\Position;
use App\Models\Division;
use App\Models\Member;
use App\Models\User;
use App\Repositories\DivisionRepository;
use App\Services\DivisionShowService;
use App\Services\MemberQueryService;
use App\Support\MemberCard;
use App\Support\MemberListProps;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class DivisionController extends Controller
{
    public function __construct(
        private DivisionRepository $division,
        private DivisionShowService $divisionShow,
        private MemberQueryService $memberQuery,
    ) {}

    public function show(Division $division): Response
    {
        return Inertia::render('division/show', $this->divisionShow->getShowData($division)->toArray());
    }

    public function partTime(Division $division): Response
    {
        $members = $division->partTimeMembers()
            ->with(['handles', 'division', 'leave'])
            ->get();

        $rows = $members->map(function (Member $member) use ($division) {
            $handle = $member->handles->firstWhere('id', $division->handle_id);
            $status = $member->division_id === 0 ? 'removed' : ($member->leave ? 'onLeave' : 'active');

            return [
                ...MemberCard::from($member),
                'primaryDivision' => $member->division_id > 0 ? $member->division?->name : null,
                'status'          => $status,
                'handle'          => $handle ? [
                    'value' => $handle->pivot->value,
                    'url'   => $handle->url ? $handle->url . $handle->pivot->value : null,
                ] : null,
                'removeUrl' => route('removePartTimer', [$division->slug, $member->clan_id]),
            ];
        })->values();

        return Inertia::render('division/part-time', [
            'division' => [
                'name'        => $division->name,
                'slug'        => $division->slug,
                'handleLabel' => $division->handle?->label,
            ],
            'members' => $rows,
            'stats'   => [
                'total'   => $members->count(),
                'active'  => $rows->where('status', 'active')->count(),
                'onLeave' => $rows->where('status', 'onLeave')->count(),
                'removed' => $rows->where('status', 'removed')->count(),
            ],
            'canManage' => request()->user()->can('recruit', Member::class),
            'addUrl'    => route('addPartTimer', $division),
        ]);
    }

    /**
     * Assign a member as part-time to a division.
     *
     * @return Redirector|RedirectResponse|string
     *
     * @throws AuthorizationException
     */
    public function assignPartTime(Division $division, Member $member)
    {
        $this->authorize('managePartTime', $member);
        $division->partTimeMembers()->attach($member->id);
        $this->showSuccessToast("{$member->name} added as part-time member to {$division->name}!");
        $member->recordActivity(ActivityType::ADD_PART_TIME, [
            'division' => $division->name,
        ]);

        return redirect()->back();
    }

    public function removePartTime(Division $division, Member $member)
    {
        $this->authorize('managePartTime', $member);
        $division->partTimeMembers()->detach($member);
        $this->showSuccessToast("{$member->name} removed from {$division->name} part-timers!");
        $member->recordActivity(ActivityType::REMOVE_PART_TIME, [
            'division' => $division->name,
        ]);

        return redirect()->back();
    }

    public function members(Division $division): Response
    {
        $includeParttimers = request()->boolean('parttimers');

        $members = $this->memberQuery->loadSortedMembers($division->members(), $division);

        if ($includeParttimers) {
            $parttimeQuery   = Member::whereHas('partTimeDivisions', fn ($q) => $q->where('division_id', $division->id));
            $parttimeMembers = $this->memberQuery->withStandardRelations($parttimeQuery, $division)
                ->with('division')
                ->get();
            $this->memberQuery->extractHandles($parttimeMembers);

            $members = $members->merge($parttimeMembers)->sortByDesc('rank');
        }

        $voiceActivityGraph = $this->division->getDivisionVoiceActivity($division);
        $unitStats          = UnitStatsData::fromMembers($members, $division, $voiceActivityGraph);

        return Inertia::render('division/members', [
            ...MemberListProps::build($division, $members, $unitStats),
            'scope'             => ['kind' => 'division', 'name' => $division->name],
            'includeParttimers' => $includeParttimers,
        ]);
    }

    public function unassignedToSquad(Division $division): JsonResponse
    {
        $this->authorize('manageUnassigned', User::class);

        $members = $division->members()
            ->with('platoon:id,name')
            ->where('platoon_id', '>', 0)
            ->where('squad_id', 0)
            ->where('position', Position::MEMBER)
            ->get(['id', 'clan_id', 'name', 'rank', 'platoon_id'])
            ->map(fn ($member) => [
                'id'         => $member->clan_id,
                'name'       => $member->present()->rankName,
                'platoon'    => $member->platoon?->name ?? 'Unknown',
                'platoon_id' => $member->platoon_id,
                'manage_url' => route('platoon', [$division, $member->platoon_id]) . '?organize=1',
            ]);

        return response()->json(['members' => $members]);
    }

    public function addPartTimer(Division $division): JsonResponse|RedirectResponse
    {
        $validated = request()->validate([
            'member_id'    => 'required|exists:members,clan_id',
            'handle_value' => 'nullable|string|max:255',
        ]);

        $member = Member::where('clan_id', $validated['member_id'])->firstOrFail();

        $this->authorize('managePartTime', $member);

        if ($division->partTimeMembers()->where('member_id', $member->id)->exists()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Member is already a part-timer'], 422);
            }
            $this->showErrorToast("{$member->name} is already a part-timer in {$division->name}");

            return redirect()->back();
        }

        $division->partTimeMembers()->attach($member->id);

        if (! empty($validated['handle_value']) && $division->handle_id) {
            $member->handles()->syncWithoutDetaching([
                $division->handle_id => ['value' => $validated['handle_value']],
            ]);
        }

        $member->recordActivity(ActivityType::ADD_PART_TIME, [
            'division' => $division->name,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$member->name} added as part-timer",
            ]);
        }

        $this->showSuccessToast("{$member->name} added as part-time member to {$division->name}!");

        return redirect()->back();
    }
}
