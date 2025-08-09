<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Platoon;
use App\Repositories\MemberRepository;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
}
