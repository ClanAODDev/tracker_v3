<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Handle;
use App\Models\Member;
use App\Models\Platoon;
use App\Repositories\MemberRepository;
use Carbon\Carbon;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Class MemberController.
 */
class MemberController extends Controller
{
    /**
     * MemberController constructor.
     */
    public function __construct(protected MemberRepository $member)
    {
        $this->middleware('auth');
    }

    /**
     * Search for a member.
     *
     * @return Factory|View
     *
     * @internal param $name
     */
    public function search($name = null)
    {
        if (! $name) {
            $name = request()->name;
        }

        if ($name) {
            $member_name = Member::where('name', 'LIKE', "%{$name}%")
                ->with('division');

            $members = Member::withWhereHas('handles', function ($query) use ($name) {
                $query->where('value', 'LIKE', "%{$name}%");
            })
                ->with('division')
                ->union($member_name)
                ->orderBy('name')
                ->get();
        } else {
            $members = [];
        }

        if (request()->ajax()) {
            return view('member.search-ajax', compact('members'));
        }

        return view('member.search', compact('members'));
    }

    /**
     * Endpoint for Bootcomplete.
     *
     * @return mixed
     */
    public function searchAutoComplete(Request $request)
    {
        $query = $request->input('query');

        $members = Member::where('name', 'LIKE', "%{$query}%")->take(5)->get();

        return $members->map(fn ($member) => [
            'id' => $member->clan_id,
            'label' => $member->name,
        ]);
    }

    public function editHandles(Member $member)
    {
        $this->authorize('manageIngameHandles', $member);

        $handles = $this->getHandles($member);

        $division = $member->division;

        return view('member.manage-ingame-handles', compact('handles', 'member', 'division'));
    }

    public function editPartTime(Member $member)
    {
        $this->authorize('managePartTime', $member);

        $division = $member->division;

        /**
         * omit divisions the member is already part-time in
         * omit member's primary division from list of available divisions.
         */
        $excludedDivisionIds = array_merge(
            $member->partTimeDivisions->pluck('id')->toArray(),
            Division::whereIn('name', ['Floater', "Bluntz' Reserves"])->pluck('id')->toArray()
        );

        $divisions = Division::active()
            ->whereNotIn('id', $excludedDivisionIds)
            ->when($member->division, fn ($query) => $query->where('id', '!=', $member->division->id))
            ->get();

        return view('member.manage-part-time', compact('member', 'division', 'divisions'));
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     *
     * @internal param int $id
     */
    public function show(Member $member)
    {
        $division = $member->division;

        // hide admin notes from non-admin users
        $notes = $member->notes()->with('author.member')->get()
            ->filter(function ($note) {
                if ($note->type === 'sr_ldr') {
                    return auth()->user()->isRole(['sr_ldr', 'admin']);
                }

                return true;
            });

        $member->load('recruits', 'recruits.division');

        $partTimeDivisions = $member->partTimeDivisions()
            ->whereActive(true)
            ->get();

        $rankHistory = $member->rankActions()->approvedAndAccepted()->get();
        $transfers = $member->transfers()->with('division')->get();

        $discordStatusLastSeen = sprintf(
            '%s &mdash; Last seen: %s',
            $member->voiceStatus,
            ($member->last_voice_activity && ! str_contains($member->last_voice_activity, '1970'))
                ? Carbon::createFromTimeString($member->last_voice_activity)->format('Y-m-d g:i A')
                : 'Never'
        );

        return view('member.show', compact(
            'member',
            'division',
            'notes',
            'partTimeDivisions',
            'rankHistory',
            'transfers',
            'discordStatusLastSeen',
        ));
    }

    /**
     * Sync player handles.
     */
    public function updateHandles(Request $request)
    {
        $member = Member::find($request->member_id);
        $handles = [];

        foreach ($request->handles as $handle) {
            $handles[$handle['id']] = [
                'value' => $handle['value'],
            ];
        }

        $member->handles()->sync($handles);
        $this->showSuccessToast('Member handles have been updated!');
    }

    public function assignPlatoon($member)
    {
        $platoon = Platoon::find(request()->platoon_id);
        $member->platoon_id = $platoon->id;
        $member->save();
    }

    public function confirmUnassign($member)
    {
        $this->authorize('reset', $member);

        $division = $member->division;

        return view('member.confirm-unassign', compact('member', 'division'));
    }

    /**
     * @return RedirectResponse
     */
    public function unassignMember($member)
    {
        $member->squad_id = 0;
        $member->platoon_id = 0;
        $member->save();

        $this->showSuccessToast('Member assignments reset successfully');

        return redirect()->route('member', $member->getUrlParams());
    }

    /**
     * @return Collection
     */
    private function getHandles(Member $member)
    {
        $handles = Handle::all()->map(function ($handle) use ($member) {
            $newHandle = [
                'id' => $handle->id,
                'label' => $handle->label,
                'type' => $handle->type,
                'comments' => $handle->comments,
                'enabled' => false,
            ];

            if ($member->handles->contains($handle->id)) {
                $newHandle['enabled'] = true;
                $newHandle['value'] = $member->handles->filter(fn ($myHandle
                ) => $handle->type === $myHandle->type)->first()->pivot->value;
            }

            return $newHandle;
        });

        return $handles->sortBy('type')->values();
    }
}
