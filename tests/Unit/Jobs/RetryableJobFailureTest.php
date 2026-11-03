<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UpdateRankForMember;
use App\Models\RankAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\CallQueuedHandler;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class RetryableJobFailureTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_queue_logs_a_retryable_job_that_gives_up(): void
    {
        Log::spy();
        $job = new UpdateRankForMember(RankAction::factory()->create());

        app(CallQueuedHandler::class)->failed(
            ['command' => serialize($job)],
            new RuntimeException('Forum rank update failed'),
            'test-uuid',
        );

        Log::shouldHaveReceived('error')->with('Job failed:', ['error' => 'Forum rank update failed'])->once();
    }
}
