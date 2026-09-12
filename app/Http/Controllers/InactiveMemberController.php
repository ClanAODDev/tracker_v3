<?php

namespace App\Http\Controllers;

use App\Data\InactiveMembersData;
use App\Enums\ActivityType;
use App\Http\Requests\Member\DeleteMember;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class InactiveMemberController extends Controller
{
    public function index(Division $division): Response
    {
        return Inertia::render('division/inactive-members', InactiveMembersData::for($division)->toArray());
    }

    public function create(Member $member): RedirectResponse
    {
        $this->authorize('flag-inactive', $member);
        $this->setFlagStatus($member, true);
        $this->showSuccessToast($member->name . ' successfully flagged for removal');

        return redirect()->back();
    }

    public function destroy(Member $member): RedirectResponse
    {
        $this->authorize('flag-inactive', $member);
        $this->setFlagStatus($member, false);
        $this->showSuccessToast($member->name . ' successfully unflagged');

        return redirect(route('division.inactive-members', $member->division->slug) . '#flagged');
    }

    public function removeMember(Member $member, DeleteMember $form): RedirectResponse
    {
        $this->authorize('separate', $member);
        $division = $member->division;
        $member->recordActivity(ActivityType::REMOVED);
        $form->persist();
        $this->showSuccessToast(ucwords($member->name) . " has been removed from the {$division->name} Division!");

        return redirect(route('division.inactive-members', [$division->slug]) . '#flagged');
    }

    public function bulkFlag(Request $request, Division $division): JsonResponse
    {
        return $this->bulkUpdateFlag($request, $division, true);
    }

    public function bulkUnflag(Request $request, Division $division): JsonResponse
    {
        return $this->bulkUpdateFlag($request, $division, false);
    }

    private function setFlagStatus(Member $member, bool $flagged): void
    {
        $member->flagged_for_inactivity = $flagged;
        $member->save();
        $member->recordActivity($flagged ? ActivityType::FLAGGED : ActivityType::UNFLAGGED);
    }

    private function bulkUpdateFlag(Request $request, Division $division, bool $flag): JsonResponse
    {
        $this->authorize('flag-inactive', Member::class);

        $validated = $request->validate([
            'member_ids'   => 'required|array',
            'member_ids.*' => 'integer',
        ]);

        $members = Member::whereIn('clan_id', $validated['member_ids'])
            ->where('division_id', $division->id)
            ->where('flagged_for_inactivity', ! $flag)
            ->get();

        $updatedIds = [];
        foreach ($members as $member) {
            $this->setFlagStatus($member, $flag);
            $updatedIds[] = $member->clan_id;
        }

        $action = $flag ? 'flagged for removal' : 'unflagged';
        $count  = count($updatedIds);

        return response()->json([
            'success'                             => true,
            'count'                               => $count,
            $flag ? 'flaggedIds' : 'unflaggedIds' => $updatedIds,
            'message'                             => $count . ' member' . ($count !== 1 ? 's' : '') . ' ' . $action,
        ]);
    }
}
