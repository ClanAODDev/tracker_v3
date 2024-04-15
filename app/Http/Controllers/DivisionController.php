<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDivision;
use App\Models\Division;
use App\Models\Member;
use App\Repositories\DivisionRepository;
use Closure;
use Exception;
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

        $division->load('unassigned.rank');

        $censusCounts = $this->division->censusCounts($division);
        $previousCensus = $censusCounts->first();
        $lastYearCensus = $censusCounts->reverse();

        $maxDays = config('app.aod.maximum_days_inactive');

        $division->outstandingInactives = $division->members()->whereDoesntHave('leave')->where(
            'last_ts_activity',
            '<',
            \Carbon\Carbon::now()->subDays($maxDays)->format('Y-m-d')
        )->count();

        $divisionLeaders = $division->leaders()->with('rank', 'position')->get();

        $platoons = $division->platoons()->with('leader.rank')->with(
            'squads.leader',
            'squads.leader.rank'
        )->withCount('members')->orderBy('order')->get();

        $generalSergeants = $division->generalSergeants()->with('rank')->get();

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
     * Show the form for editing the specified resource.
     *
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function edit(Division $division)
    {
        $this->authorize('update', $division);
        $censuses = $division->census->sortByDesc('created_at')->take(52);
        $populations = $censuses->values()->map(fn ($census, $key) => [$key, $census->count]);
        $weeklyActive = $censuses->values()->map(fn ($census, $key) => [$key, $census->weekly_active_count]);

        return view('division.modify', compact('division', 'censuses', 'weeklyActive', 'populations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     *
     * @throws Exception
     *
     * @internal param Request $request
     */
    public function update(UpdateDivision $form, Division $division)
    {
        $form->persist();
        $this->showToast('Changes saved successfully');
        $division->recordActivity('updated_settings');

        if ($division->settings()->get('slack_alert_division_edited')) {
            $division->notify(new \App\Notifications\DivisionEdited($division));
        }

        return back();
    }

    /**
     * @return Factory|View
     */
    public function partTime(Division $division)
    {
        $members = $division->partTimeMembers()->with('rank', 'handles')->get()->each(function ($member) use (
            $division
        ) {
            // filter out handles that don't match current division primary handle
            $member->handle = $member->handles->filter(fn ($handle) => $handle->id === $division->handle_id)->first();
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
        $this->showToast("{$member->name} added as part-time member to {$division->name}!");
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
        $this->showToast("{$member->name} removed from {$division->name} part-timers!");
        $member->recordActivity('remove_part_time');

        return redirect()->back();
    }

    /**
     * @return Factory|View
     */
    public function members(Division $division)
    {
        $members = $division->members()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division), 'rank', 'position', 'leave',
        ])->get()->sortByDesc('rank_id');

        $members = $members->each($this->getMemberHandle());
        $forumActivityGraph = $this->division->getDivisionActivity($division);
        $tsActivityGraph = $this->division->getDivisionTSActivity($division);
        $voiceActivityGraph = $this->division->getDivisionVoiceActivity($division);

        return view('division.members', compact('division', 'members', 'forumActivityGraph', 'tsActivityGraph', 'voiceActivityGraph'));
    }

    /**
     * Export platoon members as CSV.
     *
     * @return StreamedResponse
     */
    public function exportAsCSV(Division $division)
    {
        $members = $division->members()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division), 'rank', 'position', 'leave',
        ])->get()->sortByDesc('rank_id');

        $members = $members->each($this->getMemberHandle());

        $csv_data = $members->reduce(function ($data, $member) {
            $data[] = [
                $member->name,
                $member->rank->abbreviation,
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
            $query->where('id', $division->handle_id);
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
