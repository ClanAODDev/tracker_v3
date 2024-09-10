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
        try {
            $botAPIResponse = (new AODClient)->getForumMember($this->member->clan_id);
            $botAPIResponse = json_decode($botAPIResponse->getBody())[0];
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());

            return;
        }

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

        if ($this->member->isDirty()) {
            $this->member->save();
        }
    }
}
