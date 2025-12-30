<?php

namespace App\Http\Controllers;

use App\Data\MemberStatsData;
use App\Data\NoteStatsData;
use App\Enums\ActivityType;
use App\Models\ActivityReminder;
use App\Models\Division;
use App\Models\Member;
use App\Models\Note;
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
        $user = auth()->user();
        $canViewSrLdr = $user->isRole(['sr_ldr', 'admin']);
        $canViewTrashed = $user->can('viewTrashed', Note::class);

        $this->memberRepository->loadProfileRelations($member);
        $division = $member->division;

        $notes = $this->memberRepository->getNotesForMember($member, $canViewSrLdr);
        $trashedNotes = $canViewTrashed ? $this->memberRepository->getTrashedNotesForMember($member) : collect();
        $rankHistory = $this->memberRepository->getRankHistory($member);
        $transfers = $member->transfers;
        $partTimeDivisions = $member->partTimeDivisions;

        $memberStats = MemberStatsData::fromMember($member, $division, $this->memberRepository);
        $noteStats = NoteStatsData::fromNotes($notes);
        $rankTimeline = $this->rankTimelineService->buildTimeline($member, $rankHistory);

        return view('member.show', [
            'member' => $member,
            'division' => $division,
            'notes' => $notes,
            'trashedNotes' => $trashedNotes,
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
        $member->recordActivity(ActivityType::ASSIGNED_PLATOON, [
            'platoon' => $platoon->name,
        ]);

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
        $member->recordActivity(ActivityType::UNASSIGNED);

        $this->showSuccessToast('Member assignments reset successfully');

        return redirect()->route('member', $member->getUrlParams());
    }

    public function setActivityReminder(Member $member): JsonResponse
    {
        $this->authorize('remindActivity', $member);

        $alreadyRemindedToday = ActivityReminder::where('member_id', $member->clan_id)
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadyRemindedToday) {
            return response()->json([
                'success' => false,
                'message' => 'Already reminded today',
            ], 400);
        }

        $reminder = ActivityReminder::create([
            'member_id' => $member->clan_id,
            'division_id' => $member->division_id,
            'reminded_by_id' => auth()->id(),
        ]);

        $member->last_activity_reminder_at = $reminder->created_at;
        $member->activity_reminded_by_id = auth()->id();
        $member->save();

        return response()->json([
            'success' => true,
            'date' => $reminder->created_at->format('n/j'),
            'title' => 'Reminded ' . $reminder->created_at->diffForHumans(),
        ]);
    }

    public function clearActivityReminders(Member $member): JsonResponse
    {
        if (! auth()->user()->isRole(['sr_ldr', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (auth()->user()->member?->clan_id === $member->clan_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot clear your own reminders',
            ], 403);
        }

        $count = ActivityReminder::where('member_id', $member->clan_id)->delete();

        $member->last_activity_reminder_at = null;
        $member->activity_reminded_by_id = null;
        $member->save();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    public function bulkReminder(Division $division, Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('remindActivity', Member::class);

        $memberIds = $request->input('member_ids', []);

        if (is_string($memberIds)) {
            $memberIds = array_filter(explode(',', $memberIds));
        }

        if (empty($memberIds)) {
            if ($request->has('redirect')) {
                return redirect($request->input('redirect'))->with('error', 'No members selected');
            }

            return response()->json(['success' => false, 'message' => 'No members selected'], 400);
        }

        $alreadyRemindedToday = ActivityReminder::whereIn('member_id', $memberIds)
            ->whereDate('created_at', today())
            ->pluck('member_id')
            ->toArray();

        $toUpdate = array_diff($memberIds, $alreadyRemindedToday);

        if (empty($toUpdate)) {
            if ($request->has('redirect')) {
                return redirect($request->input('redirect'))->with('reminder_result', [
                    'count' => 0,
                    'skipped' => count($alreadyRemindedToday),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'All selected members were already reminded today',
            ], 400);
        }

        $now = now();
        $userId = auth()->id();
        $reminders = [];

        foreach ($toUpdate as $memberId) {
            $member = Member::where('clan_id', $memberId)->first();
            if ($member) {
                $reminders[] = [
                    'member_id' => $memberId,
                    'division_id' => $member->division_id,
                    'reminded_by_id' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        ActivityReminder::insert($reminders);

        $count = Member::whereIn('clan_id', $toUpdate)
            ->update([
                'last_activity_reminder_at' => $now,
                'activity_reminded_by_id' => $userId,
            ]);

        $skippedCount = count($alreadyRemindedToday);

        if ($request->has('redirect')) {
            return redirect($request->input('redirect'))->with('reminder_result', [
                'count' => $count,
                'skipped' => $skippedCount,
            ]);
        }

        return response()->json([
            'success' => true,
            'count' => $count,
            'skipped' => $skippedCount,
            'updatedIds' => $toUpdate,
            'date' => $now->format('n/j'),
        ]);
    }
}
