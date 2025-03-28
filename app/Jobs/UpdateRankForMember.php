<?php

namespace App\Jobs;

use App\AOD\Traits\Procedureable;
use App\Enums\Rank;
use App\Models\RankAction;
use App\Notifications\Channel\NotifyAdminSgtRequestComplete;
use App\Notifications\Channel\NotifyDivisionMemberPromotion;
use App\Traits\RetryableJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateRankForMember implements ShouldQueue
{
    use Procedureable;
    use Queueable;
    use RetryableJob;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public RankAction $action
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // update the forums
        if (config('app.aod.rank.update_forums')) {
            $this->callProcedure('set_user_rank', [
                $this->action->member->clan_id,
                $this->action->rank->getLabel(),
            ]);
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
