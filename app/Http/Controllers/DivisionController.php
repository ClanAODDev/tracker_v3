<?php

namespace App\Http\Controllers;

use App\Data\UnitStatsData;
use App\Enums\ActivityType;
use App\Enums\Position;
use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\Member;
use App\Repositories\DivisionRepository;
use App\Services\DivisionShowService;
use App\Services\MemberQueryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DivisionController extends Controller
{
    public function __construct(
        private DivisionRepository $division,
        private DivisionShowService $divisionShow,
        private MemberQueryService $memberQuery,
    ) {
        $this->middleware('auth');
    }

    public function show(Division $division): View
    {
        return view('division.show', $this->divisionShow->getShowData($division)->toArray());
    }

    public function applications(Division $division): JsonResponse
    {
        $this->authorize('recruit', Member::class);

        if (! $division->settings()->get('application_required', false)) {
            return response()->json(['applications' => []]);
        }

        $applications = DivisionApplication::pending()
            ->where('division_id', $division->id)
            ->with('user')
            ->latest()
            ->get()
            ->map(fn ($app) => [
                'id' => $app->id,
                'discord_username' => $app->user->discord_username,
                'created_at' => $app->created_at->diffForHumans(),
                'responses' => collect($app->responses)->map(fn ($response) => [
                    'label' => $response['label'] ?? 'Unknown',
                    'value' => $this->linkifyUrls(
                        is_array($response['value'] ?? null)
                            ? implode(', ', $response['value'])
                            : ($response['value'] ?: '—')
                    ),
                ])->values(),
            ]);

        return response()->json(['applications' => $applications]);
    }

    private function linkifyUrls(string $text): string
    {
        $escaped = e($text);
        $pattern = '/(https?:\/\/[^\s<]+)/i';

        return preg_replace_callback($pattern, fn ($matches) => sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            $matches[1],
            $matches[1]
        ), $escaped);
    }

    public function partTime(Division $division)
    {
        $members = $division->partTimeMembers()
            ->with(['handles', 'division', 'leave'])
            ->get()
            ->each(function ($member) use ($division) {
                $member->handle = $member->handles()
                    ->wherePivot('primary', true)
                    ->get()
                    ->filter(fn ($handle) => $handle->id === $division->handle_id)
                    ->first();
            });

        $stats = [
            'total' => $members->count(),
            'active' => $members->filter(fn ($m) => $m->division_id > 0 && ! $m->leave)->count(),
            'onLeave' => $members->filter(fn ($m) => $m->leave)->count(),
            'removed' => $members->filter(fn ($m) => $m->division_id === 0)->count(),
        ];

        return view('division.part-time', compact('division', 'members', 'stats'));
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

    public function members(Division $division)
    {
        $includeParttimers = request()->boolean('parttimers');

        $members = $this->memberQuery->loadSortedMembers($division->members(), $division);

        if ($includeParttimers) {
            $parttimeQuery = Member::whereHas('partTimeDivisions', fn ($q) => $q->where('division_id', $division->id));
            $parttimeMembers = $this->memberQuery->withStandardRelations($parttimeQuery, $division)
                ->with('division')
                ->get();
            $this->memberQuery->extractHandles($parttimeMembers);

            $members = $members->merge($parttimeMembers)->sortByDesc('rank');
        }

        $voiceActivityGraph = $this->division->getDivisionVoiceActivity($division);
        $unitStats = UnitStatsData::fromMembers($members, $division, $voiceActivityGraph);

        return view('division.members', compact('division', 'members', 'unitStats', 'includeParttimers'));
    }

    public function unassignedToSquad(Division $division): JsonResponse
    {
        if (! auth()->user()->isRole('sr_ldr')) {
            abort(403);
        }

        $members = $division->members()
            ->with('platoon:id,name')
            ->where('platoon_id', '>', 0)
            ->where('squad_id', 0)
            ->where('position', Position::MEMBER)
            ->get(['id', 'clan_id', 'name', 'rank', 'platoon_id'])
            ->map(fn ($member) => [
                'id' => $member->clan_id,
                'name' => $member->present()->rankName,
                'platoon' => $member->platoon?->name ?? 'Unknown',
                'platoon_id' => $member->platoon_id,
                'manage_url' => route('platoon', [$division, $member->platoon_id]) . '?organize=1',
            ]);

        return response()->json(['members' => $members]);
    }

    public function addPartTimer(Division $division): JsonResponse|RedirectResponse
    {
        $validated = request()->validate([
            'member_id' => 'required|exists:members,clan_id',
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
