<?php

namespace App\Jobs;

use App\Models\Member;
use App\Models\User;
use App\Services\AODBotService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Queue\Queueable;
use Log;

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
            $botAPIResponse = (new AODBotService)->getForumMember($this->member->clan_id);
            $botAPIResponse = json_decode($botAPIResponse->body())[0];
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return;
        }

        if (! empty($botAPIResponse->discordid)) {
            $this->member->discord_id = $botAPIResponse->discordid;

            try {
                (new AODBotService)->updateDiscordMember($this->member->discord_id);
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
            }
        }

        if (! empty($botAPIResponse->discordtag)) {
            $this->member->discord = $botAPIResponse->discordtag;
        }

        if ($this->member->isDirty()) {
            $this->member->save();
        }

        $this->linkPendingDiscordUser();
    }

    private function linkPendingDiscordUser(): void
    {
        if (! $this->member->discord_id) {
            return;
        }

        if (User::where('member_id', $this->member->id)->exists()) {
            return;
        }

        try {
            User::query()
                ->whereNull('member_id')
                ->where('discord_id', $this->member->discord_id)
                ->update(['member_id' => $this->member->id]);
        } catch (UniqueConstraintViolationException) {
            Log::warning('SyncDiscordMember: member_id already claimed by another user, skipping link', [
                'member_id'  => $this->member->id,
                'discord_id' => $this->member->discord_id,
            ]);
        }
    }
}
