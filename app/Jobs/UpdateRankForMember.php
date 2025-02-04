<?php

namespace App\Jobs;

use App\AOD\Traits\Procedureable;
use App\Models\RankAction;
use App\Notifications\Promotion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateRankForMember implements ShouldQueue
{
    use Procedureable;
    use Queueable;

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
        $this->callProcedure('set_user_rank', [
            $this->action->member->clan_id,
            convertRankToForum($this->action->rank->value),
        ]);

        // update the tracker
        $this->action->member->update([
            'rank' => $this->action->rank,
        ]);

        if ($this->action->rank->isPromotion($this->action->member->rank)) {
            $this->action->member->division->notify(new Promotion(
                $this->action->member->name,
                $this->action->rank->getLabel()
            ));
        }
    }
}
