<?php

namespace App\Http\Controllers\Division;

use App\Http\Controllers\Controller;
use App\MemberRequest;

class MemberRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (auth()->user()->isRole(['sr_ldr', 'admin'])) {
            $requests = collect([
                'denied' => request()->division->memberRequests()->denied()->get(),
                'pending' => request()->division->memberRequests()->pending()->get()
            ]);
        } else {
            $requests = collect([
                'denied' => auth()->user()->member->memberRequests()->denied()->get(),
                'pending' => auth()->user()->member->memberRequests()->pending()->get(),
            ]);
        }

        return view('division.member-requests', compact('requests'))
            ->with(['division' => request()->division]);
    }

    public function cancel($division, MemberRequest $memberRequest)
    {
        $this->authorize('cancel', $memberRequest);

        $memberRequest->cancel();

        $this->showToast('Member request has been cancelled!');

        return redirect(route('division.member-requests', $division));
    }
}
