<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Member;
use App\Repositories\MemberRepository;
use Illuminate\Http\Request;

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
     */
    public function search($name)
    {
        $members = $this->member->search($name);

        return view('member.search', compact('members'));
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
        return view('member.show', compact('member'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
