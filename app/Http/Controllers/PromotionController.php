<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateRankForMember;
use App\Models\Member;
use App\Models\RankAction;
use Carbon\Carbon;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\URL;

class PromotionController extends Controller
{
    public function confirm(Member $member, RankAction $action)
    {
        if (! request()->hasValidSignature() || $action->resolvedByRecipient()) {
            throw new InvalidSignatureException;
        }

        $expirationTime = Carbon::createFromTimestamp(request('expires'))->diffForHumans();

        $minutes = config('aod.rank.promotion_acceptance_mins');

        $acceptUrl  = URL::temporarySignedRoute('promotion.accept', now()->addMinutes($minutes), [$member->clan_id, $action]);
        $declineUrl = URL::temporarySignedRoute('promotion.decline', now()->addMinutes($minutes), [$member->clan_id, $action]);

        return view('member.promotion', compact('member', 'action', 'expirationTime', 'acceptUrl', 'declineUrl'));
    }

    public function accept(Member $member, RankAction $action)
    {
        if (! request()->hasValidSignature() || $action->resolvedByRecipient()) {
            throw new InvalidSignatureException;
        }

        $action->accept();

        UpdateRankForMember::dispatch($action);

        return view('member.promotion-confirm', compact('member', 'action'));
    }

    public function decline(Member $member, RankAction $action)
    {
        if (! request()->hasValidSignature() || $action->resolvedByRecipient()) {
            throw new InvalidSignatureException;
        }

        $action->decline();

        return view('member.promotion-confirm', compact('member', 'action'));
    }
}
