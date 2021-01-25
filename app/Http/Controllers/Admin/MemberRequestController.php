<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Notifications\MemberNameChanged;
use App\Notifications\MemberRequestApproved;
use App\Notifications\MemberRequestDenied;
use App\Notifications\MemberRequestHoldLifted;
use App\Notifications\MemberRequestPutOnHold;
use Illuminate\Auth\Access\AuthorizationException;
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
            ->with('member', 'member.rank', 'requester', 'division')
            ->get();

        $approved = MemberRequest::approved()
            ->with('member', 'member.rank', 'approver', 'division')
            ->orderBy('approved_at', 'desc')
            ->where('processed_at', null)
            ->get();

        $onHold = MemberRequest::onHold()
            ->with('member', 'member.rank', 'approver', 'division')
            ->get();

        return view('admin.member-requests', compact('pending', 'approved', 'onHold'));
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

        return view('admin.reprocess-request', compact('request'));
    }

    public function removeHold($requestId)
    {
        $this->authorize('manage', MemberRequest::class);

        $memberRequest = MemberRequest::find($requestId);

        $this->showToast('Hold removed');

        $memberRequest->removeHold();

        $memberRequest->division->notify(new MemberRequestHoldLifted($memberRequest));

        return redirect(route('admin.member-request.index'));
    }

    public function approve($requestId)
    {
        $this->authorize('manage', MemberRequest::class);

        $memberRequest = MemberRequest::find($requestId);

        if ($memberRequest->division->settings()->get('slack_alert_member_approved') == "on") {
            $memberRequest->division->notify(new MemberRequestApproved($memberRequest));
        }

        $memberRequest->approve();

        return $memberRequest;
    }

    /**
     * @param  Request  $request
     * @param  MemberRequest  $memberRequest
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
     * @param  Request  $request
     * @param  MemberRequest  $memberRequest
     * @return array
     */
    public function isAlreadyMember(Request $request, MemberRequest $memberRequest)
    {
        return ['isMember' => $memberRequest->approved_at !== null];
    }

    /**
     * @param  Request  $request
     * @param $requestId
     * @return MemberRequest
     * @throws AuthorizationException
     */
    public function placeOnHold(Request $request, $requestId)
    {
        $this->authorize('manage', MemberRequest::class);

        $request->validate([
            'notes' => 'required'
        ]);

        $memberRequest = MemberRequest::find($requestId);

        $memberRequest->placeOnHold($request->notes);

        $memberRequest->division->notify(new MemberRequestPutOnHold($memberRequest));

        return $memberRequest;
    }
}
