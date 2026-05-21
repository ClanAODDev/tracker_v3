<?php

namespace App\Jobs;

use App\Enums\ForumGroup;
use App\Models\User;
use App\Services\AODForumService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Log;
use RuntimeException;

#[Tries(3)]
#[Backoff([10, 30, 60])]
class CreateForumAccount implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $user,
        private readonly int $impersonatingMemberId,
        private readonly string $username,
        private readonly string $email,
        private readonly string $dateOfBirth,
        private readonly string $discordId,
    ) {}

    public function handle(): void
    {
        $result = AODForumService::createForumAccount(
            impersonatingMemberId: $this->impersonatingMemberId,
            username: $this->username,
            email: $this->email,
            dateOfBirth: $this->dateOfBirth,
            password: $this->user->forum_password,
            discordId: $this->discordId,
            forumGroup: ForumGroup::AWAITING_MODERATION,
        );

        if (! $result['success']) {
            Log::error('Forum account creation failed', [
                'user_id'  => $this->user->id,
                'username' => $this->username,
                'error'    => $result['error'],
            ]);

            throw new RuntimeException("Forum account creation failed for {$this->username}: {$result['error']}");
        }

        $this->user->update(['forum_password' => null]);
    }
}
