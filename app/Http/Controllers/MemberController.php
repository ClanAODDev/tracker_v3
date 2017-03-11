<?php

namespace App\Http\Controllers;

use App\Squad;
use App\Member;
use App\Platoon;
use App\Position;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\MemberRepository;

class MemberController extends Controller
{
    protected $member;

    /**
     * MemberController constructor.
     * @param MemberRepository $member
     */
    public function __construct(MemberRepository $member)
    {
        $this->member = $member;

        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Search for a member
     *
     * @param $name
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @internal param $name
     */
    public function search($name)
    {
        $members = Member::where('name', 'LIKE', "%{$name}%")
            ->with('rank')->get();

        return view('member.search', compact('members', 'request'));
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
     * Store a newly created resource in storage.
     *
     * @param Member $member
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Member $member, Request $request)
    {
        $this->authorize($member);
        dd($request->all());
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
        $division = $member->primaryDivision;

        return view('member.show', compact('member', 'division'));
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

        $positions = Position::all();
        $platoons = $member->primaryDivision->platoons()->with('leader', 'leader.rank')->get();
        $squads = $member->primaryDivision->squads()->with('leader', 'leader.rank')->get();

        return view('member.modify', compact(
            'member',
            'positions',
            'platoons',
            'squads'
        ));
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
     * Remove member from AOD
     *
     * @param Member $member
     * @param Request $request
     * @return Response
     */
    public function destroy(Member $member, Request $request)
    {
        $this->authorize('delete', $member);

        $member->recordActivity('removed');

        $member->resetPositionsAndAssignments();

        return redirect()->action('MemberController@show', [
            'clan_id' => $member->clan_id
        ]);
    }
}
