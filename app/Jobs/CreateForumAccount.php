<?php

namespace App\Jobs;

use App\Enums\ForumGroup;
use App\Models\User;
use App\Services\AODForumService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CreateForumAccount implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly User $user,
        private readonly int $impersonatingMemberId,
        private readonly string $username,
        private readonly string $email,
        private readonly string $dateOfBirth,
        private readonly string $password,
        private readonly string $discordId,
    ) {}

    public function handle(): void
    {
        $result = AODForumService::createForumAccount(
            impersonatingMemberId: $this->impersonatingMemberId,
            username: $this->username,
            email: $this->email,
            dateOfBirth: $this->dateOfBirth,
            password: $this->password,
            discordId: $this->discordId,
            forumGroup: ForumGroup::AWAITING_MODERATION,
        );

        if (! $result['success']) {
            Log::error('Forum account creation failed', [
                'user_id' => $this->user->id,
                'username' => $this->username,
                'error' => $result['error'],
            ]);

            throw new RuntimeException("Forum account creation failed for {$this->username}: {$result['error']}");
        }
    }
}
