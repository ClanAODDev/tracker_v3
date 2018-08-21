<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\MemberRequest;
use App\Notifications\MemberNameChanged;
use App\Notifications\MemberRequestApproved;
use App\Notifications\MemberRequestDenied;
use Illuminate\Http\Request;

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

        try {
            if ($request->division->settings()->get('slack_alert_member_approved') == "on") {
                $request->division->notify(new MemberRequestApproved($request));
            }
        } catch (\Exception $e) {
            //
        }

        $request->approve();

        return $request;
    }

    public function cancel($requestId)
    {
        $this->authorize('manage', MemberRequest::class);

        $this->validate(request(), [
            'notes' => 'required|max:1000'
        ], [
            'notes.required' => 'You must provide a justification!'
        ]);

        $request = MemberRequest::find($requestId);

        $request->cancel();

        try {
            if ($request->division->settings()->get('slack_alert_member_denied') == "on") {
                $request->division->notify(new MemberRequestDenied($request));
            }
        } catch (\Exception $e) {
            //
        }

        return $request;
    }

    /**
     * @param MemberRequest $memberRequest
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function requeue(MemberRequest $memberRequest)
    {
        $memberRequest->update([
            'approved_at' => null,
            'approver_id' => null,
        ]);

        $this->showToast('Request returned to pending. Cancel as appropriate.');

        return redirect(route('admin.member-request.index'));
    }

    /**
     * @param Request $request
     * @param MemberRequest $memberRequest
     */
    public function handleNameChange(Request $request, MemberRequest $memberRequest)
    {
        $member = \App\Member::whereClanId($memberRequest->member_id)
            ->first()->update([
                'name' => $request->newName
            ]);

        $memberRequest->division->notify(
            new MemberNameChanged([
                'oldName' => $request->oldName,
                'newName' => $request->newName,
            ], $memberRequest->division)
        );
    }
}
