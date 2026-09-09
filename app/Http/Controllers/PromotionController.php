<?php

namespace App\Http\Controllers;

use App\Enums\Rank;
use App\Jobs\UpdateRankForMember;
use App\Models\Member;
use App\Models\RankAction;
use Carbon\Carbon;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class PromotionController extends Controller
{
    public function confirm(Member $member, RankAction $action): Response
    {
        if (! request()->hasValidSignature() || $action->resolvedByRecipient()) {
            throw new InvalidSignatureException;
        }

        $minutes = config('aod.rank.promotion_acceptance_mins');

        return Inertia::render('member/promotion', [
            'memberName'   => $member->name,
            'rankLabel'    => $action->rank->getLabel(),
            'trainingNote' => $action->rank->value >= Rank::SERGEANT->value,
            'expiresText'  => Carbon::createFromTimestamp(request('expires'))->diffForHumans(),
            'acceptUrl'    => URL::temporarySignedRoute('promotion.accept', now()->addMinutes($minutes), [$member->clan_id, $action]),
            'declineUrl'   => URL::temporarySignedRoute('promotion.decline', now()->addMinutes($minutes), [$member->clan_id, $action]),
        ]);
    }

    public function accept(Member $member, RankAction $action): Response
    {
        if (! request()->hasValidSignature() || $action->resolvedByRecipient()) {
            throw new InvalidSignatureException;
        }

        $action->accept();

        UpdateRankForMember::dispatch($action);

        return $this->outcome($member, 'accepted');
    }

    public function decline(Member $member, RankAction $action): Response
    {
        if (! request()->hasValidSignature() || $action->resolvedByRecipient()) {
            throw new InvalidSignatureException;
        }

        $action->decline();

        return $this->outcome($member, 'declined');
    }

    private function outcome(Member $member, string $outcome): Response
    {
        return Inertia::render('member/promotion-confirm', [
            'memberName' => $member->name,
            'outcome'    => $outcome,
        ]);
    }
}
