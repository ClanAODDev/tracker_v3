<?php

namespace App\Http\Controllers\Division;

use App\Division;
use App\Http\Controllers\Controller;
use App\MemberRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return Factory|View
     */
    public function index()
    {
        if (auth()->user()->isRole(['sr_ldr', 'admin'])) {
            $requests = request()->division->memberRequests()->with('member', 'requester')->get();
        } else {
            $requests = auth()->user()->member->memberRequests()
                ->whereDivisionId(request()->division->id)
                ->with('member', 'requester')->get();
        }

        $requests = collect([
            'pending' => $requests->filter(function ($request) {
                return $request->approved_at === null && $request->cancelled_at === null;
            }),
            'cancelled' => $requests->filter(function ($request) {
                return $request->cancelled_at != null;
            }),
        ]);


        return view('division.member-requests', compact('requests'))
            ->with(['division' => request()->division]);
    }

    /**
     * @param Division $division
     * @param MemberRequest $memberRequest
     * @return MemberRequest
     * @throws AuthorizationException
     */
    public function edit(Division $division, MemberRequest $memberRequest)
    {
        $this->authorize('edit', $memberRequest);

        $memberRequest->load('member', 'requester', 'division');

        return view('division.member-request.edit', compact('memberRequest', 'division'));
    }

    public function cancel($division, MemberRequest $memberRequest)
    {
        $this->authorize('cancel', $memberRequest);

        $memberRequest->cancel();

        $this->showToast('Member request has been cancelled!');

        return redirect(route('division.member-requests.index', $division));
    }

    public function destroy(Division $division, MemberRequest $memberRequest)
    {
        $this->authorize('edit', $memberRequest);

        $memberRequest->delete();

        $this->showToast('Member request has been destroyed!');

        return redirect(route('division.member-requests.index', $division));
    }

    public function update(Division $division, MemberRequest $memberRequest, Request $request)
    {
        $this->authorize('edit', $memberRequest);

        $request->validate([
            'name' => Rule::unique('members')->ignore($memberRequest->member->id),
            'division' => 'required',
        ]);

        $memberRequest->update([
            'cancelled_at' => null,
        ]);

        $memberRequest->member->update([
            'name' => $request->name,
        ]);

        $this->showToast('Member request resubmitted!');

        return redirect(route('division.member-requests.index', $division));
    }
}
