<?php

namespace App\Http\Controllers;

use App\Models\ActivityReminder;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
class ActivityReminderController extends Controller
{
    public function store(Member $member): JsonResponse
    {
        $this->authorize('remindActivity', $member);

        $alreadyRemindedToday = ActivityReminder::where('member_id', $member->id)
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadyRemindedToday) {
            return response()->json([
                'success' => false,
                'message' => 'Already reminded today',
            ], 400);
        }

        $reminder = ActivityReminder::create([
            'member_id'      => $member->id,
            'division_id'    => $member->division_id,
            'reminded_by_id' => auth()->id(),
        ]);

        $member->last_activity_reminder_at = $reminder->created_at;
        $member->activity_reminded_by_id   = auth()->id();
        $member->save();

        return response()->json([
            'success' => true,
            'date'    => $reminder->created_at->format('n/j'),
            'title'   => 'Reminded ' . $reminder->created_at->diffForHumans(),
        ]);
    }

    public function destroy(Member $member): JsonResponse
    {
        $this->authorize('clearActivityReminders', $member);

        $count = ActivityReminder::where('member_id', $member->id)->delete();

        $member->last_activity_reminder_at = null;
        $member->activity_reminded_by_id   = null;
        $member->save();

        return response()->json([
            'success' => true,
            'count'   => $count,
        ]);
    }

    #[Authorize('remindActivity', Member::class)]
    public function bulkStore(Division $division, Request $request): JsonResponse|RedirectResponse
    {
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

        $members    = Member::whereIn('clan_id', $memberIds)->get()->keyBy('id');
        $trackerIds = $members->keys()->toArray();

        $alreadyRemindedIds = ActivityReminder::whereIn('member_id', $trackerIds)
            ->whereDate('created_at', today())
            ->pluck('member_id')
            ->toArray();

        $toUpdateIds    = array_values(array_diff($trackerIds, $alreadyRemindedIds));
        $updatedClanIds = collect($toUpdateIds)->map(fn ($id) => $members->get($id)->clan_id)->all();

        if (empty($toUpdateIds)) {
            if ($request->has('redirect')) {
                return redirect($request->input('redirect'))->with('reminder_result', [
                    'count'   => 0,
                    'skipped' => count($alreadyRemindedIds),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'All selected members were already reminded today',
            ], 400);
        }

        $now       = now();
        $userId    = auth()->id();
        $reminders = [];

        foreach ($toUpdateIds as $id) {
            $member      = $members->get($id);
            $reminders[] = [
                'member_id'      => $member->id,
                'division_id'    => $member->division_id,
                'reminded_by_id' => $userId,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        ActivityReminder::insert($reminders);

        $count = Member::whereIn('id', $toUpdateIds)
            ->update([
                'last_activity_reminder_at' => $now,
                'activity_reminded_by_id'   => $userId,
            ]);

        $skippedCount = count($alreadyRemindedIds);

        if ($request->has('redirect')) {
            return redirect($request->input('redirect'))->with('reminder_result', [
                'count'   => $count,
                'skipped' => $skippedCount,
            ]);
        }

        return response()->json([
            'success'    => true,
            'count'      => $count,
            'skipped'    => $skippedCount,
            'updatedIds' => $updatedClanIds,
            'date'       => $now->format('n/j'),
        ]);
    }
}
