<?php

namespace App\Http\Controllers;

use App\Division;
use App\Http\Requests\UpdateDivision;
use App\Member;
use App\Notifications\DivisionEdited;
use App\Repositories\DivisionRepository;
use App\Tag;
use Carbon\Carbon;
use CL\Slack\Payload\ChannelsCreatePayload;
use Illuminate\Support\Facades\Log;

/**
 * Class DivisionController
 *
 * @package App\Http\Controllers
 */
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

    public function storeSlackChannel(Division $division)
    {
        $this->authorize('createSlackChannels', auth()->user());

        $payload = new ChannelsCreatePayload();
        $channelName = str_slug(request()->get('division') . "-" . request()->get('channel-name'));

        $payload->setName($channelName);

        $response = $this->client->send($payload);

        if ($response->isOk()) {
            $this->showToast("{$channelName} was created!");
            Log::info(auth()->user()->name
                . " created a slack channel - {$channelName} - "
                . Carbon::now());

            return redirect()->back();
        } else {
            return redirect()->back()->withErrors([
                'slack-error' => $response->getError(),
                'slack-error-detail' => $response->getErrorExplanation()
            ])->withInput();
        }

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
        $platoons = $division->platoons()
            ->with('leader.rank')
            ->with('squads.leader', 'squads.leader.rank')
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
        $division->recordActivity('updated_settings');

        if ($division->settings()->get('slack_alert_division_edited')) {
            $division->notify(new DivisionEdited($division));
        }

        return back();
    }

    /**
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function partTime(Division $division)
    {
        $members = $division->partTimeMembers()->with('rank', 'handles')->get()
            ->each(function ($member) use ($division) {
                // filter out handles that don't match current division primary handle
                $member->handles = $member->handles->filter(function ($handle) use ($division) {
                    return $handle->id === $division->handle_id;
                });
            });

        return view('division.part-time', compact('division', 'members'));
    }

    /**
     * Assign a member as part-time to a division
     *
     * @param Division $division
     * @param Member $member
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|string
     */
    public function assignPartTime(Division $division, Member $member)
    {
        $this->authorize('update', $member);
        $division->partTimeMembers()->attach($member->id);
        $this->showToast("{$member->name} added as part-time member to {$division->name}!");

        $member->recordActivity('add_part_time');

        return redirect()->back();
    }

    /**
     * @param Division $division
     * @param Member $member
     * @return \Illuminate\Http\RedirectResponse|string
     */
    public function removePartTime(Division $division, Member $member)
    {
        $this->authorize('update', $member);
        $division->partTimeMembers()->detach($member);
        $this->showToast("{$member->name} removed from {$division->name} part-timers!");

        $member->recordActivity('remove_part_time');

        return redirect()->back();
    }

    /**
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function census(Division $division)
    {
        $censuses = $division->census->sortByDesc('created_at')->take(52);

        $populations = $censuses->values()->map(function ($census, $key) {
            return [$census->javascriptTimestamp, $census->count];
        });

        $weeklyActive = $censuses->values()->map(function ($census, $key) {
            return [$census->javascriptTimestamp, $census->weekly_active_count];
        });

        $weeklyTsActive = $censuses->values()->map(function ($census, $key) {
            return [$census->javascriptTimestamp, $census->weekly_ts_count];
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
            'comments', 'censuses', 'weeklyTsActive'
        ));
    }

    /**
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showTsReport(Division $division)
    {
        $issues = $division->mismatchedTSMembers;

        return view('division.ts-report', compact('division', 'issues'));
    }

    /**
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function members(Division $division)
    {
        $members = $division->members()->with('rank', 'position', 'leave')->get();
        $forumActivityGraph = $this->division->getDivisionActivity($division);
        $tsActivityGraph = $this->division->getDivisionTSActivity($division);

        return view('division.members', compact(
            'division', 'members', 'forumActivityGraph', 'tsActivityGraph'
        ));
    }
}
