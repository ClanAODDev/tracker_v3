<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\MemberAward;
use App\Rules\UniqueAwardForMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $divisionSlug = request('division');

        $query = Award::active()
            ->orderBy('display_order')
            ->withCount('recipients')
            ->with('division');

        if ($divisionSlug) {
            $query->whereHas('division', fn (Builder $q) => $q->where('slug', $divisionSlug));
        }

        $allAwards = $query->get();

        $awards = $allAwards->filter(function ($award) {
            if ($award->division_id === null) {
                return true;
            }
            if ($award->division?->active) {
                return true;
            }

            return $award->recipients_count > 0;
        });

        if ($divisionSlug && $awards->isEmpty()) {
            $this->showErrorToast('Selected division has no awards assigned. Showing all...');

            return redirect(route('awards.index'));
        }

        $maxRecipients = $awards->max('recipients_count') ?: 1;
        $awards->each(function ($award) use ($maxRecipients) {
            $award->popularityPct = round($award->recipients_count / $maxRecipients * 100);
            $award->rarity = $award->getRarity();
        });

        $clanAwards = $awards->whereNull('division_id')->values();

        $activeAwards = $awards
            ->whereNotNull('division_id')
            ->filter(fn ($award) => $award->division?->active)
            ->groupBy('division.name');

        $legacyAwards = $awards
            ->whereNotNull('division_id')
            ->filter(fn ($award) => ! $award->division?->active && $award->recipients_count > 0)
            ->groupBy('division.name');

        $activeAndClanAwards = $awards->filter(fn ($a) => $a->division_id === null || $a->division?->active);
        $totals = (object) [
            'awards' => $awards->count(),
            'recipients' => $awards->sum('recipients_count'),
            'requestable' => $activeAndClanAwards->where('allow_request', true)->count(),
        ];

        return view('division.awards.index', compact(
            'awards',
            'clanAwards',
            'activeAwards',
            'legacyAwards',
            'totals',
            'divisionSlug'
        ));
    }

    public function show(Award $award)
    {
        $award->load(['division']);
        $award->loadCount('recipients');

        $recipients = MemberAward::where('award_id', $award->id)
            ->where('approved', true)
            ->with(['member:clan_id,name,rank,division_id', 'member.division:id,name,slug'])
            ->orderByDesc('created_at')
            ->paginate(50);

        $stats = (object) [
            'total' => $recipients->total(),
            'firstAwarded' => MemberAward::where('award_id', $award->id)
                ->where('approved', true)
                ->orderBy('created_at')
                ->first()?->created_at,
            'lastAwarded' => MemberAward::where('award_id', $award->id)
                ->where('approved', true)
                ->orderByDesc('created_at')
                ->first()?->created_at,
            'rarity' => $award->getRarity(),
        ];

        return view('division.awards.show', compact('award', 'recipients', 'stats'));
    }

    public function storeRecommendation(Request $request, Award $award)
    {
        if (! $award->allow_request) {
            return redirect()->back()->withErrors(['award' => 'Award requests are not allowed for this award.']);
        }

        if ($award->division && ! $award->division->active) {
            return redirect()->back()->withErrors(['award' => 'This is a legacy award and cannot be requested.']);
        }

        $validatedData = $request->validate([
            'reason' => 'required|string|max:255',
            'member_id' => [
                'required',
                'numeric',
                'exists:members,clan_id',
                new UniqueAwardForMember($award->id),
            ],
        ]);

        MemberAward::create([
            'requester_id' => auth()->user()->member_id,
            'award_id' => $award->id,
            'member_id' => $validatedData['member_id'],
            'reason' => $validatedData['reason'],
        ]);

        $this->showSuccessToast('Your award request has been submitted successfully.');

        return redirect()->back();
    }
}
