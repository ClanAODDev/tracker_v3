<?php

namespace App\Jobs;

use App\Models\Member;
use App\Services\AODClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncDiscordMember implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Member $member) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $botAPIResponse = $this->member->forumMember();

        if (! empty($botAPIResponse->discordid)) {
            $this->member->discord_id = $botAPIResponse->discordid;

            try {
                (new AODClient)->updateDiscordMember($this->member->discord_id);
            } catch (GuzzleException $exception) {
                \Log::error($exception->getMessage());
            }
        }

        if (! empty($botAPIResponse->discordtag)) {
            $this->member->discord = $botAPIResponse->discordtag;
        }

        $this->member->save();
    }
}
