<?php

namespace App\Http\Controllers;

use App\Data\UnitStatsData;
use App\Models\Division;
use App\Models\Member;
use App\Repositories\DivisionRepository;
use App\Services\DivisionShowService;
use App\Services\MemberQueryService;
use Illuminate\Auth\Access\AuthorizationException;
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
        $unitStats = UnitStatsData::fromMembers($members, $division, $voiceActivityGraph);

        return view('division.members', compact('division', 'members', 'unitStats', 'includeParttimers'));
    }
}
