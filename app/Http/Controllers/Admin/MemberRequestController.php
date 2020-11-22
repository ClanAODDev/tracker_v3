<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
use Illuminate\Support\Facades\Http;
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
     * @throws AuthorizationException
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

    public function removeHold(MemberRequest $memberRequest)
    {
        $this->authorize('manage', MemberRequest::class);

        $this->showToast('Hold removed');

        $memberRequest->removeHold();

        $memberRequest->division->notify(new MemberRequestHoldLifted());

        return redirect(route('admin.member-request.index'));
    }

    public function approve(MemberRequest $memberRequest)
    {
        $this->authorize('manage', MemberRequest::class);

        /*        try {
                    $this->approveOnAOD($memberRequest);
                } catch (\Exception $exception) {
                    return response("An error occurred and the request could not be processed", 400);
                };
        */

        if ($memberRequest->division->settings()->get('slack_alert_member_approved') == "on") {
            $memberRequest->division->notify(new MemberRequestApproved());
        }

        $memberRequest->approve();

        return $memberRequest;
    }

    public function cancel(MemberRequest $memberRequest)
    {
        $this->authorize('manage', MemberRequest::class);

        request()->validate([
            'notes' => 'required|max:1000'
        ], [
            'notes.required' => 'You must provide a justification!'
        ]);

        $memberRequest->cancel();

        if ($memberRequest->division->settings()->get('slack_alert_member_denied') == "on") {
            $memberRequest->division->notify(new MemberRequestDenied());
        }

        return $memberRequest;
    }

    /**
     * @param  Request  $request
     * @param  MemberRequest  $memberRequest
     */
    public function handleNameChange(MemberRequest $memberRequest)
    {
        $memberRequest->member()->update([
            'name' => request()->newName
        ]);

        $memberRequest->division->notify(
            new MemberNameChanged([
                'oldName' => $memberRequest->oldName,
                'newName' => $memberRequest->newName,
            ], $memberRequest->division)
        );
    }

    /**
     * @param  Request  $request
     * @param  MemberRequest  $memberRequest
     * @return array
     */
    public function isAlreadyMember(MemberRequest $memberRequest)
    {
        return ['isMember' => $memberRequest->approved_at !== null];
    }

    /**
     * @param  Request  $request
     * @param  MemberRequest  $memberRequest
     * @return MemberRequest
     * @throws AuthorizationException
     */
    public function placeOnHold(MemberRequest $memberRequest)
    {
        $this->authorize('manage', MemberRequest::class);

        request()->validate([
            'notes' => 'required'
        ]);

        $memberRequest->placeOnHold(request()->get('notes'));

        $memberRequest->division->notify(new MemberRequestPutOnHold($memberRequest));

        return $memberRequest;
    }

    /**
     * WIP
     * @param $requestId
     */
    private function approveOnAOD($requestId)
    {
        dd($requestId);
        Http::post("https://www.clanaod.net/forums/modcp/aodmember.php?do=clickaddaod&u=50283");
    }
}
