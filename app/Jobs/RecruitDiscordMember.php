<?php

namespace App\Jobs;

use App\Enums\Rank;
use App\Models\Division;
use App\Models\User;
use App\Services\RecruitmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RecruitDiscordMember implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $forumName,
        public string $divisionName,
        public Rank $rank,
        public int $platoonId,
        public ?int $squadId,
        public ?string $ingameName,
        public int $recruiterId
    ) {}

    public function handle(RecruitmentService $recruitmentService): void
    {
        $division = Division::where('name', $this->divisionName)->first();

        if (! $division) {
            Log::error('RecruitDiscordMember: Division not found', ['division' => $this->divisionName]);

            return;
        }

        $clanId = $this->createForumAccount();

        if (! $clanId) {
            Log::warning('RecruitDiscordMember: Forum account creation failed, skipping member creation', [
                'user_id' => $this->user->id,
                'forum_name' => $this->forumName,
            ]);

            return;
        }

        $member = $recruitmentService->createMember(
            $clanId,
            $this->forumName,
            $division,
            $this->rank->value,
            $this->platoonId,
            $this->squadId,
            $this->ingameName,
            $this->recruiterId
        );

        $recruitmentService->createMemberRequest($member, $division, $this->recruiterId);

        $this->user->member_id = $member->id;
        $this->user->save();

        SyncDiscordMember::dispatch($member);

        Log::info('RecruitDiscordMember: Successfully recruited Discord member', [
            'user_id' => $this->user->id,
            'member_id' => $member->id,
            'clan_id' => $clanId,
        ]);
    }

    private function createForumAccount(): ?int
    {
        Log::info('RecruitDiscordMember: Creating forum account', [
            'discord_id' => $this->user->discord_id,
            'discord_username' => $this->user->discord_username,
            'email' => $this->user->email,
            'forum_name' => $this->forumName,
            'division' => $this->divisionName,
            'rank' => $this->rank->getLabel(),
        ]);

        return null;
    }
}
