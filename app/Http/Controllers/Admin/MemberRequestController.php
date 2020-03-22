<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Member;
use App\MemberRequest;
use App\Notifications\MemberNameChanged;
use App\Notifications\MemberRequestApproved;
use App\Notifications\MemberRequestDenied;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

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
     * @return Factory|View
     */
    public function index()
    {
        $this->authorize('manage', MemberRequest::class);

        $pending = MemberRequest::pending()
            ->pastGracePeriod()
            ->with('member', 'member.rank', 'requester', 'division')
            ->get();

        $approved = MemberRequest::approved()
            ->with('member', 'member.rank', 'approver', 'division')
            ->orderBy('approved_at', 'desc')
            ->where('processed_at', null)
            ->get();

        return view('admin.member-requests', compact('pending', 'approved'));
    }

    /**
     * @param $requestId
     * @return Factory|RedirectResponse|Redirector|View
     */
    public function reprocess($requestId)
    {
        $request = MemberRequest::find($requestId)
            ->load('member', 'member.rank', 'approver', 'division');

        if (request()->isMethod('post')) {
            $request->update([
                'approved_at' => now(),
                'approver_id' => auth()->user()->member->clan_id
            ]);

            $this->showToast('Request updated!');

            return redirect(route('admin.member-request.index'));
        }

        return view('admin.reprocess-request', compact(
            'pending',
            'approved',
            'request'
        ));
    }

    public function approve($requestId)
    {
        $this->authorize('manage', MemberRequest::class);

        $request = MemberRequest::find($requestId);

        if ($request->division->settings()->get('slack_alert_member_approved') == "on") {
            $request->division->notify(new MemberRequestApproved($request));
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

        if ($request->division->settings()->get('slack_alert_member_denied') == "on") {
            $request->division->notify(new MemberRequestDenied($request));
        }

        return $request;
    }

    /**
     * @param Request $request
     * @param MemberRequest $memberRequest
     */
    public function handleNameChange(Request $request, MemberRequest $memberRequest)
    {
        $member = Member::whereClanId($memberRequest->member_id)
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

    /**
     * @param Request $request
     * @param MemberRequest $memberRequest
     * @return array
     */
    public function isAlreadyMember(Request $request, MemberRequest $memberRequest)
    {
        return ['isMember' => $memberRequest->approved_at !== null];
    }
}
