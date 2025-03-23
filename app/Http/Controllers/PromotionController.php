<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateRankForMember;
use App\Models\Member;
use App\Models\RankAction;
use Carbon\Carbon;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class PromotionController extends Controller
{
    public function confirm(Member $member, RankAction $action)
    {
        if (! request()->hasValidSignature() || $action->resolved()) {
            throw new InvalidSignatureException;
        }

        $expirationTime = Carbon::createFromTimestamp(request('expires'))->diffForHumans();

        return view('member.promotion', compact('member', 'action', 'expirationTime'));
    }

    public function accept(Member $member, RankAction $action)
    {
        if ($action->resolved()) {
            return redirect()->route('home');
        }

        $action->accept();

        UpdateRankForMember::dispatch($action);

        return view('member.promotion-confirm', compact('member', 'action'));
    }

    public function decline(Member $member, RankAction $action)
    {
        if ($action->resolved()) {
            return redirect()->route('home');
        }

        $action->decline();

        return view('member.promotion-confirm', compact('member', 'action'));
    }
}
