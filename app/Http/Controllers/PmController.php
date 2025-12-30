<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendBulkPm;
use App\Models\Member;

class PmController extends Controller
{
    public function create(SendBulkPm $request)
    {
        $validated = $request->validated();
        $memberIds = explode(',', $validated['pm-member-data']);

        $membersSelected = Member::whereIn('clan_id', $memberIds)
            ->select('clan_id', 'allow_pm', 'name')
            ->get();

        $availableForPm = $membersSelected->filter(function ($member) {
            return $member->allow_pm;
        });

        $remindedCount = 0;
        $skippedCount = 0;
        if ($request->boolean('set_reminder') && $request->user()->can('remindActivity', Member::class)) {
            $alreadyRemindedToday = Member::whereIn('clan_id', $memberIds)
                ->whereDate('last_activity_reminder_at', today())
                ->count();

            $remindedCount = Member::whereIn('clan_id', $memberIds)
                ->where(function ($query) {
                    $query->whereNull('last_activity_reminder_at')
                        ->orWhereDate('last_activity_reminder_at', '<', today());
                })
                ->update([
                    'last_activity_reminder_at' => now(),
                    'activity_reminded_by_id' => auth()->id(),
                ]);

            $skippedCount = $alreadyRemindedToday;
        }

        return view('division.create-pm')->with([
            'members' => $availableForPm,
            'omitted' => $membersSelected->diffAssoc($availableForPm),
            'division' => $request->division,
            'remindedCount' => $remindedCount,
            'skippedCount' => $skippedCount,
        ]);
    }
}
