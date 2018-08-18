<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\MemberRequest;

class MemberRequestController extends Controller
{
    /**
     * MemberRequestController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('manage', MemberRequest::class);

        $pending = MemberRequest::pending()
            ->with('member', 'member.rank', 'requester', 'division')
            ->get();

        $approved = MemberRequest::approved()
            ->with('member', 'member.rank', 'approver', 'division')
            ->get();

        return view('admin.member-requests', compact('pending', 'approved'));
    }

    public function approve(MemberRequest $memberRequest)
    {
        $this->authorize('manage', MemberRequest::class);

        $memberRequest->approve();

        return $request;
    }

    public function deny($requestId)
    {
        $this->authorize('manage', MemberRequest::class);

        $request = MemberRequest::findOrFail($requestId);

        $request->deny();

        return $request;
    }
}
