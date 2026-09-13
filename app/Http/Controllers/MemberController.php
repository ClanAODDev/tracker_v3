<?php

namespace App\Http\Controllers;

use App\Data\MemberProfileData;
use App\Enums\ActivityType;
use App\Models\Member;
use App\Models\Platoon;
use App\Repositories\MemberRepository;
use App\Services\RankTimelineService;
use App\Support\MemberCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class MemberController extends Controller
{
    public function __construct(
        private MemberRepository $memberRepository,
        private RankTimelineService $rankTimelineService,
    ) {}

    public function search(Request $request): Response|JsonResponse
    {
        $query   = $request->query('q');
        $members = $query ? $this->memberRepository->search($query) : collect();

        $results = $members->values()->map(fn (Member $member) => [
            'rankName'   => $member->present()->rankName(),
            'clanId'     => $member->clan_id,
            'division'   => $member->division->name ?? 'Ex-AOD',
            'profileUrl' => route('member', $member->getUrlParams()),
            'discord'    => $member->discord,
            'handle'     => $member->handles->first()
                ? $member->handles->first()->pivot->value . ' [' . $member->handles->first()->label . ']'
                : null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['results' => $results]);
        }

        return Inertia::render('member/search', [
            'query'   => $query,
            'results' => $results,
        ]);
    }

    public function searchAutoComplete(Request $request)
    {
        return $this->memberRepository->searchAutocomplete($request->input('query'));
    }

    public function show(Member $member): Response
    {
        return Inertia::render(
            'member/show',
            MemberProfileData::for($member, $this->memberRepository, $this->rankTimelineService)->toArray(),
        );
    }

    #[Authorize('recruit', Member::class)]
    public function assignPlatoon(Member $member): JsonResponse
    {
        $platoon = Platoon::where('id', request()->platoon_id)
            ->where('division_id', $member->division_id)
            ->firstOrFail();

        $member->platoon_id = $platoon->id;
        $member->save();
        $member->recordActivity(ActivityType::ASSIGNED_PLATOON, [
            'platoon' => $platoon->name,
        ]);

        return response()->json(['success' => true]);
    }

    public function confirmUnassign(Member $member): Response
    {
        $this->authorize('reset', $member);

        return Inertia::render('member/confirm-unassign', [
            'member'   => MemberCard::from($member),
            'resetUrl' => route('member.unassign', $member->clan_id),
        ]);
    }

    public function unassignMember(Member $member): RedirectResponse
    {
        $this->authorize('reset', $member);

        $member->squad_id   = 0;
        $member->platoon_id = 0;
        $member->save();
        $member->recordActivity(ActivityType::UNASSIGNED);

        $this->showSuccessToast('Member assignments reset successfully');

        return redirect()->route('member', $member->getUrlParams());
    }
}
