<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Platoon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function search($name = null)
    {
        $name = $name ?: request()->name;

        $members = [];

        if ($name) {
            $byName = Member::where('name', 'LIKE', "%{$name}%")->with('division');

            $members = Member::withWhereHas('handles', fn ($query) => $query->where('value', 'LIKE', "%{$name}%"))
                ->with('division')
                ->union($byName)
                ->orderBy('name')
                ->get();
        }

        if (request()->ajax()) {
            return view('member.search-ajax', compact('members'));
        }

        return view('member.search', compact('members'));
    }

    public function searchAutoComplete(Request $request)
    {
        return Member::where('name', 'LIKE', "%{$request->input('query')}%")
            ->take(5)
            ->get()
            ->map(fn ($member) => [
                'id' => $member->clan_id,
                'label' => $member->name,
            ]);
    }

    public function show(Member $member)
    {
        $division = $member->division;

        $notes = $member->notes()->with('author.member')->get()
            ->filter(fn ($note) => $note->type !== 'sr_ldr' || auth()->user()->isRole(['sr_ldr', 'admin']));

        $member->load('recruits', 'recruits.division');

        return view('member.show', [
            'member' => $member,
            'division' => $division,
            'notes' => $notes,
            'partTimeDivisions' => $member->partTimeDivisions()->whereActive(true)->get(),
            'rankHistory' => $member->rankActions()->approvedAndAccepted()->get(),
            'transfers' => $member->transfers()->with('division')->get(),
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
