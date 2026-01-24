<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateDivisionForMember;
use App\Models\Division;
use App\Models\Transfer;
use App\Notifications\Channel\NotifyDivisionMemberTransferRequested;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberTransferController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $member = auth()->user()->member;

        if (! $member) {
            return response()->json(['error' => 'No member record found'], 400);
        }

        $floaterDivision = Division::where('name', 'Floater')->first();
        if ($floaterDivision && $member->division_id === $floaterDivision->id) {
            return response()->json(['error' => 'You cannot transfer from your current division'], 400);
        }

        $request->validate([
            'division_id' => 'required|exists:divisions,id',
        ]);

        $targetDivision = Division::find($request->division_id);

        if (! $targetDivision || ! $targetDivision->active || $targetDivision->shutdown_at) {
            return response()->json(['error' => 'Target division is not available for transfers'], 400);
        }

        if ($targetDivision->id === $member->division_id) {
            return response()->json(['error' => 'You cannot transfer to your current division'], 400);
        }

        $excludedTargetDivisions = Division::whereIn('name', ['Floater', "Bluntz' Reserves"])
            ->pluck('id')
            ->toArray();

        if (in_array($targetDivision->id, $excludedTargetDivisions)) {
            return response()->json(['error' => 'You cannot transfer to this division'], 400);
        }

        if (Transfer::where('member_id', $member->id)->pending()->exists()) {
            return response()->json(['error' => 'You already have a pending transfer request'], 400);
        }

        $recentTransfer = Transfer::where('member_id', $member->id)
            ->where('created_at', '>=', now()->subWeek())
            ->first();

        if ($recentTransfer) {
            $nextAllowed = $recentTransfer->created_at->addWeek()->format('M j, Y');

            return response()->json([
                'error' => "Transfer requests can only be made once per week. You can request again after {$nextAllowed}.",
            ], 400);
        }

        $isOfficer = $member->rank->isOfficer();
        $oldDivision = $member->division;

        $transfer = Transfer::create([
            'member_id' => $member->id,
            'division_id' => $targetDivision->id,
        ]);

        $notifications = [
            [$oldDivision, 'OUTGOING'],
            [$targetDivision, 'INCOMING'],
        ];

        foreach ($notifications as [$division, $type]) {
            $division->notify(new NotifyDivisionMemberTransferRequested(
                $member,
                $targetDivision->name,
                $type
            ));
        }

        if (! $isOfficer) {
            $transfer->approve();
            UpdateDivisionForMember::dispatch($transfer);
        }

        return response()->json([
            'success' => true,
            'auto_approved' => ! $isOfficer,
            'message' => $isOfficer
                ? 'Transfer request submitted. Awaiting approval from ' . $targetDivision->name . ' leadership.'
                : 'You have been transferred to ' . $targetDivision->name,
        ]);
    }
}
