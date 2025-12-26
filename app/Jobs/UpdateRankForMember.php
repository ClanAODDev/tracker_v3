<?php

namespace App\Jobs;

use App\Enums\Rank;
use App\Models\RankAction;
use App\Notifications\Channel\NotifyAdminSgtRequestComplete;
use App\Notifications\Channel\NotifyDivisionMemberPromotion;
use App\Services\ForumProcedureService;
use App\Traits\RetryableJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateRankForMember implements ShouldQueue
{
    use Queueable;
    use RetryableJob;

    public function __construct(
        public RankAction $action
    ) {}

    public function handle(ForumProcedureService $procedureService): void
    {
        if (config('aod.rank.update_forums')) {
            $procedureService->setUserRank(
                $this->action->member->clan_id,
                $this->action->rank->getLabel()
            );
        }

        if ($this->action->rank->value >= Rank::SERGEANT->value) {
            $this->action->rank->notify(new NotifyAdminSgtRequestComplete(
                $this->action->member->name,
                $this->action->rank->getLabel()
            ));
        }

        if ($this->action->rank->isPromotion($this->action->member->rank)) {
            // notifying of promotion
            $this->action->member->division->notify(new NotifyDivisionMemberPromotion(
                $this->action->member->name,
                $this->action->rank->getLabel()
            ));
        }

        // update the tracker
        $isPromotion = $this->action->rank->isPromotion($this->action->member->rank);

        $this->action->member->update([
            'rank' => $this->action->rank,
            'last_promoted_at' => $isPromotion ? now() : $this->action->member->last_promoted_at,
        ]);

    }
}
