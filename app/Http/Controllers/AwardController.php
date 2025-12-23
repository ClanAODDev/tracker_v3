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

        $awards = $query->get();

        if ($divisionSlug && $awards->isEmpty()) {
            $this->showErrorToast('Selected division has no awards assigned. Showing all...');

            return redirect(route('awards.index'));
        }

        $maxRecipients = $awards->max('recipients_count') ?: 1;
        $awards->each(function ($award) use ($maxRecipients) {
            $award->popularityPct = round($award->recipients_count / $maxRecipients * 100);
            $award->rarity = $this->calculateRarity($award->recipients_count);
        });

        $clanAwards = $awards->whereNull('division_id')->values();
        $divisionAwards = $awards->whereNotNull('division_id')->groupBy('division.name');

        $totals = (object) [
            'awards' => $awards->count(),
            'recipients' => $awards->sum('recipients_count'),
            'requestable' => $awards->where('allow_request', true)->count(),
        ];

        return view('division.awards.index', compact(
            'awards',
            'clanAwards',
            'divisionAwards',
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
            'rarity' => $this->calculateRarity($award->recipients_count),
        ];

        return view('division.awards.show', compact('award', 'recipients', 'stats'));
    }

    public function storeRecommendation(Request $request, Award $award)
    {
        if (! $award->allow_request) {
            return redirect()->back()->withErrors(['award' => 'Award requests are not allowed for this award.']);
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

    private function calculateRarity(int $count): string
    {
        return match (true) {
            $count === 0 => 'mythic',
            $count <= 10 => 'legendary',
            $count <= 30 => 'epic',
            $count <= 50 => 'rare',
            default => 'common',
        };
    }
}
