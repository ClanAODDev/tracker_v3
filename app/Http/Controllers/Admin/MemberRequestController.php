<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Position;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Notifications\MemberNameChanged;
use App\Notifications\MemberRequestApproved;
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

        if ($this->isDivisionLeadership()) {
            $pending = $this->filterByDivision($pending);
            $approved = $this->filterByDivision($approved);
            $onHold = $this->filterByDivision($onHold);
        }

        return view('admin.member-requests', compact('pending', 'approved', 'onHold'));
    }

    public function history()
    {
        $this->authorize('manage', MemberRequest::class);

        $requests = MemberRequest::where('approved_at', '>=', now()->subDays(3))
            ->with('member', 'member.rank', 'approver', 'division')
            ->orderByDesc('approved_at')
            ->get();

        if ($this->isDivisionLeadership()) {
            $requests = $this->filterByDivision($requests);
        }

        return view('admin.requests-history', compact('requests'));
    }

    /**
     * @param $requestId
     * @return Factory|Redirector|RedirectResponse|View
     */
    public function reprocess($requestId)
    {
        $request = MemberRequest::find($requestId)
            ->load('member', 'member.rank', 'approver', 'division');

        if (request()->isMethod('post')) {
            $request->update([
                'approved_at' => now(),
                'approver_id' => auth()->user()->member->clan_id,
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

        if ('on' === $memberRequest->division->settings()->get('slack_alert_member_approved')) {
            $memberRequest->division->notify(new MemberRequestApproved($memberRequest));
        }

        $memberRequest->approve();

        return $memberRequest;
    }

    public function handleNameChange(Request $request, MemberRequest $memberRequest)
    {
        $member = Member::whereClanId($memberRequest->member_id)
            ->first()->update([
                'name' => $request->newName,
            ]);

        $memberRequest->division->notify(
            new MemberNameChanged([
                'oldName' => $request->oldName,
                'newName' => $request->newName,
            ])
        );
    }

    /**
     * @return array
     */
    public function isAlreadyMember(Request $request, MemberRequest $memberRequest)
    {
        return ['isMember' => null !== $memberRequest->approved_at];
    }

    /**
     * @param $requestId
     * @return MemberRequest
     *
     * @throws AuthorizationException
     */
    public function placeOnHold(Request $request, $requestId)
    {
        $this->authorize('manage', MemberRequest::class);

        $request->validate([
            'notes' => 'required',
        ]);

        $memberRequest = MemberRequest::find($requestId);

        $memberRequest->placeOnHold($request->notes);

        $memberRequest->division->notify(new MemberRequestPutOnHold($memberRequest));

        return $memberRequest;
    }

    /**
     * @param $requests
     * @return mixed
     */
    private function filterByDivision($requests)
    {
        return $requests->reject(function ($request) {
            return $request->division_id !== auth()->user()->member->division_id;
        })->values();
    }

    private function isDivisionLeadership(): bool
    {
        return auth()->user()->isRole('senior leader')
            && \in_array(auth()->user()->member->position, [
                Position::EXECUTIVE_OFFICER,
                Position::COMMANDING_OFFICER,
            ]);
    }
}
