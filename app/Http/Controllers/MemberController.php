<?php

namespace App\Http\Controllers;

use App\Data\MemberStatsData;
use App\Data\NoteStatsData;
use App\Models\Member;
use App\Models\Platoon;
use App\Repositories\MemberRepository;
use App\Services\RankTimelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(
        private MemberRepository $memberRepository,
        private RankTimelineService $rankTimelineService,
    ) {
        $this->middleware('auth');
    }

    public function search($name = null)
    {
        $name = $name ?: request()->name;
        $members = $name ? $this->memberRepository->search($name) : collect();

        if (request()->ajax()) {
            return view('member.search-ajax', compact('members'));
        }

        return view('member.search', compact('members'));
    }

    public function searchAutoComplete(Request $request)
    {
        return $this->memberRepository->searchAutocomplete($request->input('query'));
    }

    public function show(Member $member)
    {
        $division = $member->division;
        $canViewSrLdr = auth()->user()->isRole(['sr_ldr', 'admin']);

        $this->memberRepository->loadProfileRelations($member);

        $notes = $this->memberRepository->getNotesForMember($member, $canViewSrLdr);
        $rankHistory = $this->memberRepository->getRankHistory($member);
        $transfers = $this->memberRepository->getTransfers($member);
        $partTimeDivisions = $this->memberRepository->getPartTimeDivisions($member);

        $memberStats = MemberStatsData::fromMember($member, $division, $this->memberRepository);
        $noteStats = NoteStatsData::fromNotes($notes);
        $rankTimeline = $this->rankTimelineService->buildTimeline($member, $rankHistory);

        return view('member.show', [
            'member' => $member,
            'division' => $division,
            'notes' => $notes,
            'noteStats' => $noteStats,
            'partTimeDivisions' => $partTimeDivisions,
            'rankHistory' => $rankHistory,
            'rankTimeline' => $rankTimeline,
            'transfers' => $transfers,
            'memberStats' => $memberStats,
        ]);
    }

    public function assignPlatoon(Member $member): JsonResponse
    {
        $platoon = Platoon::find(request()->platoon_id);
        $member->platoon_id = $platoon->id;
        $member->save();

        return response()->json(['success' => true]);
    }

    public function confirmUnassign(Member $member)
    {
        $this->authorize('reset', $member);

        return view('member.confirm-unassign', [
            'member' => $member,
            'division' => $member->division,
        ]);
    }

    public function unassignMember(Member $member): RedirectResponse
    {
        $member->squad_id = 0;
        $member->platoon_id = 0;
        $member->save();

        $this->showSuccessToast('Member assignments reset successfully');

        return redirect()->route('member', $member->getUrlParams());
    }
}
