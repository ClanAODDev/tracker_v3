<?php

namespace App\Http\Controllers;

use App\Data\MemberProfileData;
use App\Enums\ActivityType;
use App\Filament\Forms\Components\DivisionMemberFieldsForm;
use App\Http\Requests\Member\SearchMembers;
use App\Http\Requests\Member\UpdateMemberDetails;
use App\Models\Member;
use App\Models\Unit;
use App\Repositories\MemberRepository;
use App\Services\MemberHandleService;
use App\Services\RankTimelineService;
use App\Services\Units\UnitAssignment;
use App\Support\MemberCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    public function search(SearchMembers $request): Response|JsonResponse
    {
        $query   = trim((string) $request->validated('q'));
        $partial = $request->isPartial($query);

        if ($request->wantsJson()) {
            $members = $query !== '' ? $this->memberRepository->search($query, $partial) : collect();

            return response()->json(['results' => $members->values()->map(fn (Member $member) => $this->searchResult($member))]);
        }

        $paginator = $query !== ''
            ? $this->memberRepository->searchPaginated($query, $partial)->withQueryString()
            : null;

        return Inertia::render('member/search', [
            'query'      => $query !== '' ? $query : null,
            'results'    => $paginator ? $paginator->getCollection()->values()->map(fn (Member $member) => $this->searchResult($member)) : [],
            'partial'    => $partial,
            'pagination' => $paginator ? [
                'total'       => $paginator->total(),
                'currentPage' => $paginator->currentPage(),
                'lastPage'    => $paginator->lastPage(),
                'from'        => $paginator->firstItem(),
                'to'          => $paginator->lastItem(),
                'prevUrl'     => $paginator->previousPageUrl(),
                'nextUrl'     => $paginator->nextPageUrl(),
            ] : null,
        ]);
    }

    private function searchResult(Member $member): array
    {
        return [
            'rankName'   => $member->present()->rankName(),
            'clanId'     => $member->clan_id,
            'division'   => $member->division->name ?? 'Ex-AOD',
            'profileUrl' => route('member', $member->getUrlParams()),
            'discord'    => $member->discord,
            'handle'     => $member->handles->first()
                ? $member->handles->first()->pivot->value . ' [' . $member->handles->first()->label . ']'
                : null,
        ];
    }

    public function searchAutoComplete(SearchMembers $request)
    {
        $query = trim((string) $request->validated('query'));

        return $this->memberRepository->searchAutocomplete($query, partial: $request->isPartial($query));
    }

    public function show(Member $member): Response
    {
        return Inertia::render(
            'member/show',
            MemberProfileData::for($member, $this->memberRepository, $this->rankTimelineService)->toArray(),
        );
    }

    public function updateDetails(UpdateMemberDetails $request, Member $member, MemberHandleService $handles): JsonResponse
    {
        if ($request->has('handles') && $request->user()->can('manageHandles', $member)) {
            $handles->sync($member, $request->input('handles', []));
        }

        if ($request->has('fields') && $request->user()->can('manageFields', $member)) {
            DivisionMemberFieldsForm::saveValues($member, $request->input('fields', []));
        }

        return response()->json(['success' => true]);
    }

    #[Authorize('recruit', Member::class)]
    public function assignPlatoon(Member $member): JsonResponse
    {
        $platoon = Unit::query()
            ->whereNull('parent_id')
            ->where('division_id', $member->division_id)
            ->findOrFail(request()->platoon_id);

        $current = $member->unit;
        app(UnitAssignment::class)->assignMember($member, $current && str_starts_with($current->path, $platoon->path) ? $current : $platoon, recordActivity: false);
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

        app(UnitAssignment::class)->assignMember($member, null);

        $this->showSuccessToast('Member assignments reset successfully');

        return redirect()->route('member', $member->getUrlParams());
    }
}
