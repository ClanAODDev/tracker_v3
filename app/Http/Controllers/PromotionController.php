<?php

namespace App\Http\Controllers;

use App\Enums\Rank;
use App\Jobs\UpdateRankForMember;
use App\Models\Member;
use App\Models\RankAction;
use App\Notifications\Channel\NotifyAdminSgtRequestComplete;
use App\Notifications\Channel\NotifyDivisionMemberPromotion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class PromotionController extends Controller
{
    public function confirm(Request $request, Member $member, RankAction $action)
    {
        if (! $request->hasValidSignature() || $action->resolved()) {
            throw new InvalidSignatureException;
        }

        $expirationTime = Carbon::createFromTimestamp(request('expires'))->diffForHumans();

        return view('member.promotion', compact('member', 'action', 'expirationTime'));
    }

    public function accept(Member $member, RankAction $action)
    {
        $action->accept();

        $this->showSuccessToast('You have accepted your promotion! Your rank will be updated shortly.');

        UpdateRankForMember::dispatch($action);

        return redirect()->route('home');
    }

    public function decline(Member $member, RankAction $action)
    {
        $action->decline();

        $this->showInfoToast('You have declined your promotion.');

        return redirect()->route('home');
    }
}
