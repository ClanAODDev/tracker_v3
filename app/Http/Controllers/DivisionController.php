<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDivision;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
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
 * Class DivisionController
 *
 * @package App\Http\Controllers
 */
class DivisionController extends \App\Http\Controllers\Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  DivisionRepository  $division
     */
    public function __construct(\App\Repositories\DivisionRepository $division)
    {
        $this->division = $division;
        $this->middleware(['auth', 'activeDivision']);
    }

    /**
     * Display the specified resource.
     *
     * @param  Division  $division
     * @return Response
     * @internal param int $id
     */
    public function show(\App\Models\Division $division)
    {
        $division->load('unassigned.rank');
        $censusCounts = $this->division->censusCounts($division);
        $previousCensus = $censusCounts->first();
        $lastYearCensus = $censusCounts->reverse();
        $maxDays = config('app.aod.maximum_days_inactive');
        $division->outstandingInactives = $division->members()->whereDoesntHave('leave')->where(
            'last_activity',
            '<',
            \Carbon\Carbon::now()->subDays($maxDays)->format('Y-m-d')
        )->count();
        $divisionLeaders = $division->leaders()->with('rank', 'position')->get();
        $platoons = $division->platoons()->with('leader.rank')->with(
            'squads.leader',
            'squads.leader.rank'
        )->withCount('members')->orderBy('order')->get();
        $generalSergeants = $division->generalSergeants()->with('rank')->get();
        $staffSergeants = $division->staffSergeants()->with('rank')->get();
        return view(
            'division.show',
            compact(
                'division',
                'previousCensus',
                'platoons',
                'lastYearCensus',
                'divisionLeaders',
                'generalSergeants',
                'staffSergeants'
            )
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Division  $division
     * @return Response
     * @throws AuthorizationException
     */
    public function edit(\App\Models\Division $division)
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
     * @param  UpdateDivision  $form
     * @param  Division  $division
     * @return Response
     * @throws Exception
     * @internal param Request $request
     */
    public function update(\App\Http\Requests\UpdateDivision $form, \App\Models\Division $division)
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
     * @param  Division  $division
     * @return Factory|View
     */
    public function partTime(\App\Models\Division $division)
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
     * Assign a member as part-time to a division
     *
     * @param  Division  $division
     * @param  Member  $member
     * @return RedirectResponse|Redirector|string
     * @throws AuthorizationException
     */
    public function assignPartTime(\App\Models\Division $division, \App\Models\Member $member)
    {
        $this->authorize('managePartTime', $member);
        $division->partTimeMembers()->attach($member->id);
        $this->showToast("{$member->name} added as part-time member to {$division->name}!");
        $member->recordActivity('add_part_time');
        return redirect()->back();
    }

    /**
     * @param  Division  $division
     * @param  Member  $member
     * @return RedirectResponse|string
     * @throws AuthorizationException
     */
    public function removePartTime(\App\Models\Division $division, \App\Models\Member $member)
    {
        $this->authorize('managePartTime', $member);
        $division->partTimeMembers()->detach($member);
        $this->showToast("{$member->name} removed from {$division->name} part-timers!");
        $member->recordActivity('remove_part_time');
        return redirect()->back();
    }

    /**
     * @param  Division  $division
     * @return Factory|View
     */
    public function members(\App\Models\Division $division)
    {
        $members = $division->members()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division), 'rank', 'position', 'leave'
        ])->get()->sortByDesc('rank_id');

        $members = $members->each($this->getMemberHandle());
        $forumActivityGraph = $this->division->getDivisionActivity($division);
        $tsActivityGraph = $this->division->getDivisionTSActivity($division);

        return view('division.members', compact('division', 'members', 'forumActivityGraph', 'tsActivityGraph'));
    }

    /**
     * Export platoon members as CSV
     *
     * @param  Division  $division
     * @return StreamedResponse
     */
    public function exportAsCSV(Division $division)
    {
        $members = $division->members()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division), 'rank', 'position', 'leave'
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
                'Last TS Activity',
                'Last Promoted',
                'Member Handle',
                'Member Forum Posts'
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
     * @param $division
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
