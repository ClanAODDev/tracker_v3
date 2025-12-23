<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Member;
use App\Repositories\DivisionRepository;
use App\Services\MemberQueryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

class DivisionController extends Controller
{
    public function __construct(
        private DivisionRepository $division,
        private MemberQueryService $memberQuery,
    ) {
        $this->middleware('auth');
    }

    /**
     * Display the specified resource.
     *
     * @return Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\View|Response
     *
     * @internal param int $id
     */
    public function show(Division $division)
    {
        $divisionAnniversaries = $this->division->getDivisionAnniversaries($division);

        $censusCounts = $this->division->censusCounts($division);
        $previousCensus = $censusCounts->first();
        $lastYearCensus = $censusCounts->reverse();

        $maxDays = config('aod.maximum_days_inactive');

        $division->outstandingInactives = $division->members()->whereDoesntHave('leave')->where(
            'last_voice_activity',
            '<',
            \Carbon\Carbon::now()->subDays($maxDays)->format('Y-m-d')
        )->count();

        $division->outstandingAwardRequests = $division->awards()->whereHas('unapprovedRecipients')->count();

        $divisionLeaders = $division->leaders()->get();

        $platoons = $division->platoons()->with(
            'squads.leader',
        )->withCount('members')->orderBy('order')->get();

        $generalSergeants = $division->generalSergeants()->get();

        return view(
            'division.show',
            compact(
                'division',
                'previousCensus',
                'platoons',
                'lastYearCensus',
                'divisionLeaders',
                'generalSergeants',
                'divisionAnniversaries',
            )
        );
    }

    /**
     * @return Factory|View
     */
    public function partTime(Division $division)
    {
        $members = $division->partTimeMembers()->with('handles')
            ->get()->each(function ($member) use ($division) {
                // filter out handles that don't match current division primary handle
                $member->handle = $member->handles()->wherePivot('primary', true)->get()->filter(fn ($handle
                ) => $handle->id === $division->handle_id)->first();
            });

        return view('division.part-time', compact('division', 'members'));
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
        $member->recordActivity('add_part_time');

        return redirect()->back();
    }

    /**
     * @return RedirectResponse|string
     *
     * @throws AuthorizationException
     */
    public function removePartTime(Division $division, Member $member)
    {
        $this->authorize('managePartTime', $member);
        $division->partTimeMembers()->detach($member);
        $this->showSuccessToast("{$member->name} removed from {$division->name} part-timers!");
        $member->recordActivity('remove_part_time');

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

        return view('division.members', compact('division', 'members', 'voiceActivityGraph', 'includeParttimers'));
    }
}
