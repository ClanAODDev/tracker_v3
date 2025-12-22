<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Member;
use App\Repositories\DivisionRepository;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class DivisionController.
 */
class DivisionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(DivisionRepository $division)
    {
        $this->division = $division;
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

        $maxDays = config('app.aod.maximum_days_inactive');

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

    /**
     * @return Factory|View
     */
    public function members(Division $division)
    {
        $members = $division->members()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division), 'leave', 'tags',
        ])->get()->sortByDesc('rank');

        $members = $members->each($this->getMemberHandle());
        $voiceActivityGraph = $this->division->getDivisionVoiceActivity($division);

        return view('division.members', compact('division', 'members', 'voiceActivityGraph'));
    }

    /**
     * Export platoon members as CSV.
     *
     * @return StreamedResponse
     */
    public function exportAsCSV(Division $division)
    {
        $members = $division->members()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division), 'leave',
        ])->get()->sortByDesc('rank');

        $members = $members->each($this->getMemberHandle());

        $csv_data = $members->reduce(function ($data, $member) {
            $data[] = [
                $member->name,
                $member->rank->getAbbreviation(),
                $member->join_date,
                $member->last_activity,
                $member->last_ts_activity,
                $member->last_promoted_at,
                $member->handle->pivot->value ?? 'N/A',
                $member->posts,
            ];

            return $data;
        }, [
            [
                'Name',
                'Rank',
                'Join Date',
                'Last Forum Activity',
                'Last Comms Activity',
                'Last Promoted',
                'Member Handle',
                'Member Forum Posts',
            ],
        ]);

        return new StreamedResponse(function () use ($csv_data) {
            $handle = fopen('php://output', 'w');
            foreach ($csv_data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=members.csv',
        ]);
    }

    /**
     * @return Closure
     */
    private function filterHandlesToPrimaryHandle($division)
    {
        return function ($query) use ($division) {
            $query->where('handles.id', $division->handle_id)
                ->wherePivot('primary', true);
        };
    }

    /**
     * @return Closure
     */
    private function getMemberHandle()
    {
        return function ($member) {
            $member->handle = $member->handles->first();
        };
    }
}
