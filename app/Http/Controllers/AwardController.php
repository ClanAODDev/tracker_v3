<?php

namespace App\Http\Controllers;

use App\Data\AwardIndexData;
use App\Data\AwardShowData;
use App\Models\Award;
use App\Models\Member;
use App\Models\MemberAward;
use App\Rules\UniqueAwardForMember;
use App\Services\AwardCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class AwardController extends Controller
{
    public function index(AwardCatalogService $catalog): Response|RedirectResponse
    {
        $divisionSlug = request('division');
        $data         = AwardIndexData::for($divisionSlug, $catalog);

        if ($divisionSlug && $data->isEmpty()) {
            $this->showErrorToast('Selected division has no awards assigned. Showing all...');

            return redirect(route('awards.index'));
        }

        return Inertia::render('awards/index', $data->toArray());
    }

    public function tiered(string $slug, AwardCatalogService $catalog): Response
    {
        $tieredGroups = $catalog->buildTieredGroups();
        $group        = collect($tieredGroups)->firstWhere('slug', $slug);

        abort_unless($group, 404);

        $tierIds = $group['tiers']->pluck('id')->toArray();
        $tiers   = Award::whereIn('id', $tierIds)
            ->withCount('recipients')
            ->orderBy('display_order')
            ->get();

        $userMember = auth()->user()?->member;

        $userAwards = $userMember
            ? MemberAward::where('member_id', $userMember->id)
                ->whereIn('award_id', $tierIds)
                ->where('approved', true)
                ->get()
                ->keyBy('award_id')
            : collect();

        $userAwardIds = $userAwards->pluck('award_id')->toArray();
        $earnedCount  = count($userAwardIds);
        $totalTiers   = $tiers->count();

        $nextTierId = $tiers->firstWhere(fn ($tier) => ! in_array($tier->id, $userAwardIds))?->id;

        $firstAwarded = MemberAward::whereIn('award_id', $tierIds)
            ->where('approved', true)
            ->orderBy('created_at')
            ->first()?->created_at;

        return Inertia::render('awards/tiered', [
            'group' => [
                'name'        => $group['name'],
                'slug'        => $group['slug'],
                'description' => $group['description'],
            ],
            'stats' => [
                'totalRecipients' => $group['recipientCount'],
                'firstAwarded'    => $firstAwarded?->format('M Y'),
                'earnedCount'     => $earnedCount,
                'totalTiers'      => $totalTiers,
                'progressPct'     => $totalTiers > 0 ? round(($earnedCount / $totalTiers) * 100) : 0,
            ],
            'tiers' => $tiers->map(function ($tier) use ($userAwards, $userAwardIds, $nextTierId, $group) {
                $earned = in_array($tier->id, $userAwardIds);

                return [
                    'id'              => $tier->id,
                    'name'            => $tier->name,
                    'description'     => $tier->description,
                    'rarity'          => $tier->getRarity(),
                    'recipientsCount' => (int) $tier->recipients_count,
                    'image'           => $tier->image ? $tier->getImagePath() : null,
                    'earned'          => $earned,
                    'isNext'          => $tier->id === $nextTierId,
                    'earnedDate'      => $earned ? $userAwards->get($tier->id)?->created_at?->format('M j, Y') : null,
                    'pct'             => $group['recipientCount'] > 0
                        ? round(($tier->recipients_count / $group['recipientCount']) * 100)
                        : 0,
                ];
            }),
        ]);
    }

    public function show(Award $award): Response
    {
        return Inertia::render('awards/show', AwardShowData::for($award)->toArray());
    }

    public function storeRecommendation(Request $request, Award $award): RedirectResponse
    {
        if (! $award->canBeRequestedBy()) {
            return redirect()->back()->withErrors(['award' => 'You cannot request this award.']);
        }

        $validatedData = $request->validate([
            'reason'    => 'required|string|max:255',
            'member_id' => [
                'required',
                'numeric',
                'exists:members,clan_id',
                new UniqueAwardForMember($award->id),
            ],
        ]);

        $member = Member::whereClanId($validatedData['member_id'])->firstOrFail();

        MemberAward::create([
            'requester_id' => auth()->user()->member_id,
            'award_id'     => $award->id,
            'member_id'    => $member->id,
            'reason'       => $validatedData['reason'],
        ]);

        $this->showSuccessToast('Your award request has been submitted successfully.');

        return redirect()->back();
    }
}
