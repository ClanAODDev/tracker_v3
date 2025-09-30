<?php

namespace App\Http\Controllers;

use App\Enums\Position;
use App\Enums\Rank;
use App\Exceptions\FactoryMissingException;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function __construct(\App\Repositories\ClanRepository $clanRepository)
    {
        $this->middleware('auth');
        $this->clan = $clanRepository;
    }

    /**
     * @return Factory|View
     */
    public function clanCensusReport()
    {
        $memberCount = $this->clan->totalActiveMembers();

        if (! $this->clan->censusCounts()->count()) {
            throw new FactoryMissingException('You might need to run the `census` factory');
        }

        // get our census information, and organize it
        $censusCounts = $this->clan->censusCounts();
        $previousCensus = $censusCounts->first();
        $lastYearCensus = $censusCounts->reverse();

        // fetch all divisions and eager load census data
        $censuses = \App\Models\Division::active()->orderBy('name')->withoutFloaters()
            ->with('census')->get()->filter(fn (
                $division
            ) => \count($division->census))->each(function ($division) {
                $division->total = $division->census->last()->count;
                $division->popMinusVoiceActive = $division->census->last()->count - $division->census->last()
                    ->weekly_voice_count;
                $division->weeklyVoiceActive = $division->census->last()->weekly_voice_count;
            });

        // break down rank distribution
        $rankDemographic = collect($this->clan->allRankDemographic());
        $rankDemographic = $rankDemographic->map(function ($rank) use ($memberCount) {
            $rank->difference = $memberCount - $rank->count;

            return $rank;
        });

        return view('reports.clan-statistics')->with(compact(
            'memberCount',
            'previousCensus',
            'lastYearCensus',
            'memberCount',
            'censuses',
            'rankDemographic',
        ));
    }

    public function outstandingMembersReport()
    {
        $clanMax = config('app.aod.maximum_days_inactive');
        $divisions = \App\Models\Division::active()->orderBy('name')->withCount('members')->get();
        $divisions->map(function ($division) use ($clanMax) {
            $divisionMax = $division->settings()->get('inactivity_days');
            $members = $division->members()->whereDoesntHave('leave', function ($q) {
                $q->whereDate('end_date', '>', today());
            })->get();
            $outstandingCount = $members->where(
                'last_voice_activity',
                '<',
                \Carbon\Carbon::now()->subDays($clanMax)->format('Y-m-d')
            )->count();
            $inactiveCount = $members->where(
                'last_voice_activity',
                '<',
                \Carbon\Carbon::now()->subDays($divisionMax)->format('Y-m-d')
            )->count();
            $division->outstanding_members = $outstandingCount;
            $division->inactive_members = $inactiveCount;
            $division->percent_inactive = number_format($inactiveCount / max($division->members_count, 1) * 100, 1);

            return $division;
        });

        return view('reports.outstanding-members', compact('divisions'));
    }

    /**
     * Users with empty discord tag.
     */
    public function usersWithoutDiscordReport()
    {
        $divisions = \App\Models\Division::active()->get();
        $data = [];
        foreach ($divisions as $division) {
            foreach ($division->members->where('discord', '') as $member) {
                $data[$division->name][] = [$member->clan_id => "{$member->name}"];
            }
        }

        return $data;
    }

    public function divisionUsersWithAccess()
    {
        foreach (\App\Models\Division::active()->get() as $division) {
            echo '---------- ' . $division->name . ' ---------- ' . PHP_EOL;
            $members = $division->members()->whereHas('user', function ($query) {
                $query->where('role_id', '>', 2);
            })->get();
            $sortedMembers = collect(\Illuminate\Support\Arr::sort($members, fn ($member) => $member->rank_id));
            $sortedMembers->each(function ($member) {
                echo $member->present()->rankName() . ", {$member->user->role_id}" . PHP_EOL;
            });
            echo '---------- END OF DIVISION ----------' . PHP_EOL . PHP_EOL . PHP_EOL;
        }
    }

    /**
     * @return mixed
     */
    public function divisionTurnoverReport()
    {
        $divisions = \App\Models\Division::active()->withCount(
            'members',
            'newMembersLast30',
            'newMembersLast60',
            'newMembersLast90'
        )->get();

        return view('reports.division-turnover', compact('divisions'));
    }

    /**
     * @return Factory|View
     */
    public function leadership()
    {
        $divisions = \App\Models\Division::active()
            ->with([
                'sergeants' => function ($query) {
                    $query
                        ->orderByRaw('
                    CASE 
                        WHEN position = ? THEN 9999
                        ELSE -position 
                    END ASC
                ', [Position::CLAN_ADMIN->value])
                        ->orderByDesc('rank');
                },
            ])
            ->withCount('sgtAndSsgt')
            ->get();

        $leadership = \App\Models\Member::query()
            ->where('rank', '>', Rank::STAFF_SERGEANT)
            ->where('division_id', '!=', 0)
            ->orderByDesc('rank')
            ->orderBy('name')
            ->get();

        return view('reports.leadership', compact('divisions', 'leadership'));
    }
}
