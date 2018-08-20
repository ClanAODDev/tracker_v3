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

    public function approve($requestId)
    {
        $this->authorize('manage', MemberRequest::class);

        $request = MemberRequest::find($requestId);

        $request->approve();

        return $request;
    }

    public function cancel($requestId)
    {
        $this->authorize('manage', MemberRequest::class);

        $this->validate(request(), [
            'notes' => 'required|max:1000'
        ]);

        $request = MemberRequest::find($requestId);

        $request->cancel();

        return $request;
    }
}
