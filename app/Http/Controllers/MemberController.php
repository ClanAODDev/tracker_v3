<?php

namespace App\Http\Controllers;

use App\Division;
use App\Handle;
use App\Http\Requests\DeleteMember;
use App\Member;
use App\Notifications\MemberRemoved;
use App\Position;
use App\Repositories\MemberRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Toastr;

/**
 * Class MemberController
 *
 * @package App\Http\Controllers
 */
class MemberController extends Controller
{
    /**
     * @var MemberRepository
     */
    protected $member;

    /**
     * MemberController constructor.
     *
     * @param MemberRepository $member
     */
    public function __construct(MemberRepository $member)
    {
        $this->member = $member;

        $this->middleware('auth');
    }

    /**
     * Search for a member
     *
     * @param $name
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @internal param $name
     */
    public function search($name = null)
    {
        if ( ! $name) {
            $name = request()->name;
        }

        if ($name) {
            $members = Member::where('name', 'LIKE', "%{$name}%")
                ->with('rank', 'division')->get();
        } else {
            $members = [];
        }

        if (request()->ajax()) {
            return view('member.search-ajax', compact('members'));
        }

        return view('member.search', compact('members'));
    }

    /**
     * Endpoint for Bootcomplete
     *
     * @param Request $request
     * @return mixed
     */
    public function searchAutoComplete(Request $request)
    {
        $query = $request->input('query');

        $members = Member::where('name', 'LIKE', "%{$query}%")->take(5)->get();

        return $members->map(function ($member) {
            return [
                'id' => $member->clan_id,
                'label' => $member->name
            ];
        });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Member $member
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function show(Member $member)
    {
        $division = $member->division;

        // hide admin notes from non-admin users
        $notes = $member->notes()->with('author', 'tags')->get()
            ->filter(function ($note) {
                if ($note->type == 'sr_ldr') {
                    return auth()->user()->isRole(['sr_ldr', 'admin']);
                }

                return true;
            });

        $partTimeDivisions = $member->partTimeDivisions()
            ->whereActive(true)
            ->get();

        return view('member.show', compact(
            'member', 'division', 'notes', 'partTimeDivisions'
        ));
    }

    /**
     * Assigns a position to the given member
     *
     * @param Request $request
     */
    public function updatePosition(Request $request)
    {
        $member = Member::find($request->member);
        $this->authorize('update', $member);
        $member->assignPosition(Position::find($request->position));
        $member->save();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Member $member
     * @return \Illuminate\Http\Response
     */
    public function edit(Member $member)
    {
        $this->authorize('update', $member);

        $division = $member->division;
        $positions = Position::all()->pluck('id', 'name');

        /**
         * omit divisions the member is already part-time in
         * omit member's primary division from list of available divisions
         **/
        $divisions = Division::active()->get()->except(
            $member->partTimeDivisions->pluck('id')->toArray()
        )->filter(function ($division) use ($member) {
            if ($member->division) {
                return $division->id !== $member->division->id;
            }

            return $division;
        });

        $handles = $this->getHandles($member);

        return view('member.edit-member', compact(
            'member', 'division', 'positions', 'handles', 'divisions'
        ));
    }

    /**
     * @param Member $member
     * @return \Illuminate\Support\Collection
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
                $newHandle['value'] = $member->handles->filter(function ($myHandle) use ($handle) {
                    return $handle->type === $myHandle->type;
                })->first()->pivot->value;
            }

            return $newHandle;
        });

        return $handles->sortBy('type')->values();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Sync player handles
     *
     * @param Request $request
     */
    public function updateHandles(Request $request)
    {
        $member = Member::find($request->member_id);
        $handles = [];

        foreach ($request->handles as $handle) {
            $handles[$handle['id']] = [
                'value' => $handle['value']
            ];
        }

        $member->handles()->sync($handles);
        $this->showToast('Member handles have been updated!');
    }

    /**
     * Remove member from AOD
     *
     * @param Member $member
     * @param DeleteMember $form
     * @return Response
     */
    public function destroy(Member $member, DeleteMember $form)
    {
        $division = $member->division;

        if ($division->settings()->get('slack_alert_removed_member')) {
            $division->notify(new MemberRemoved($member, $form->input('removal-reason')));
        }

        $form->persist();

        $this->showToast(
            ucwords($member->name) . " has been removed from the {$division->name} Division!"
        );

        return redirect()->route('division', [
            $division->abbreviation
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sergeants()
    {
        $divisions = Division::active()
            ->with('sergeants', 'sergeants.rank', 'sergeants.position')
            ->with('staffSergeants', 'staffSergeants.rank')
            ->withCount('members')
            ->withCount('sergeants')
            ->orderBy('name')
            ->get();

        return view('member.sergeants', compact('divisions'));
    }
}
