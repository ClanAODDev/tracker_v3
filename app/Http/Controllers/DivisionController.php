<?php

namespace App\Http\Controllers;

use App\Division;
use App\Http\Requests\UpdateDivision;
use App\Member;
use App\Notifications\DivisionEdited;
use App\Repositories\DivisionRepository;
use App\Tag;

class DivisionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param DivisionRepository $division
     */
    public function __construct(DivisionRepository $division)
    {
        $this->division = $division;

        $this->middleware(['auth', 'activeDivision']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Display the specified resource.
     *
     * @param Division $division
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function show(Division $division)
    {
        $censusCounts = $this->division->censusCounts($division);
        $previousCensus = $censusCounts->first();
        $lastYearCensus = $censusCounts->reverse();

        $divisionLeaders = $division->leaders()->with('rank', 'position')->get();
        $platoons = $division->platoons()->with('leader.rank')
            ->withCount('members')->orderBy('order')->get();

        $generalSergeants = $division->generalSergeants()->with('rank')->get();
        $staffSergeants = $division->staffSergeants()->with('rank')->get();

        return view('division.show', compact(
            'division', 'previousCensus', 'platoons', 'lastYearCensus',
            'divisionLeaders', 'generalSergeants', 'staffSergeants'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Division $division
     * @return \Illuminate\Http\Response
     */
    public function edit(Division $division)
    {
        $this->authorize('update', $division);

        $censuses = $division->census->sortByDesc('created_at')->take(52);

        $populations = $censuses->values()->map(function ($census, $key) {
            return [$key, $census->count];
        });

        $weeklyActive = $censuses->values()->map(function ($census, $key) {
            return [$key, $census->weekly_active_count];
        });

        $defaultTags = Tag::whereDefault(true)->get();

        return view('division.modify', compact(
            'division', 'censuses', 'weeklyActive',
            'populations', 'comments', 'defaultTags'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDivision $form
     * @param Division $division
     * @return \Illuminate\Http\Response
     * @internal param Request $request
     */
    public function update(UpdateDivision $form, Division $division)
    {
        $form->persist();

        $this->showToast('Changes saved successfully');

        if ($division->settings()->get('slack_alert_division_edited')) {
            $division->notify(new DivisionEdited($division));
        }

        return back();
    }

    /**
     * Assign a member as part-time to a division
     *
     * @param Division $division
     * @param Member $member
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function assignPartTime(Division $division, Member $member)
    {
        $division->partTimeMembers()->sync($member->id, ['primary' => false]);
        $this->showToast("{$member->name} added as part-time member to {$division->name}!");

        return redirect()->back();
    }

    public function removePartTime(Division $division, Member $member)
    {
        $division->partTimeMembers()->detach($member);
        $this->showToast("{$member->name} removed from {$division->name} part-timers!");

        return redirect()->back();
    }

    public function partTime(Division $division)
    {
        $partTime = $division->partTimeMembers;

        return view('division.part_time', compact('division', 'partTime'));
    }

    public function census(Division $division)
    {
        $censuses = $division->census->sortByDesc('created_at')->take(52);

        $populations = $censuses->values()->map(function ($census, $key) {
            return [$key, $census->count];
        });

        $weeklyActive = $censuses->values()->map(function ($census, $key) {
            return [$key, $census->weekly_active_count];
        });

        $comments = $censuses->values()
            ->filter(function ($census) use ($censuses) {
                return ($census->notes);
            })->map(function ($census, $key) use ($censuses) {
                return [
                    'x' => $key,
                    'y' => $censuses->values()->pluck('count'),
                    'contents' => $census->notes
                ];
            })->values();

        return view('division.census', compact(
            'division', 'populations', 'weeklyActive',
            'comments', 'censuses'
        ));
    }
}
