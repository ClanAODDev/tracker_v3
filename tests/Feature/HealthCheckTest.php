<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false, 'queue.default' => 'database']);
    }

    private function recordSchedulerRun(string $column, \DateTimeInterface $at): void
    {
        MonitoredScheduledTask::create([
            'name'            => 'tracker:member-sync',
            'type'            => 'command',
            'cron_expression' => '0 * * * *',
            $column           => $at,
        ]);
    }

    private function queueJob(int $minutesAgo): void
    {
        DB::table('jobs')->insert([
            'queue'        => 'default',
            'payload'      => '{}',
            'attempts'     => 0,
            'reserved_at'  => null,
            'available_at' => now()->subMinutes($minutesAgo)->getTimestamp(),
            'created_at'   => now()->subMinutes($minutesAgo)->getTimestamp(),
        ]);
    }

    #[Test]
    public function it_is_up_when_the_database_queue_and_scheduler_are_healthy(): void
    {
        $this->recordSchedulerRun('last_started_at', now()->subMinutes(5));
        $this->queueJob(1);

        $this->getJson('/up')->assertOk()->assertJson(['status' => 'up']);
    }

    #[Test]
    public function it_is_down_when_a_job_has_waited_too_long(): void
    {
        $this->recordSchedulerRun('last_started_at', now()->subMinutes(5));
        $this->queueJob(30);

        $this->getJson('/up')->assertStatus(500)->assertJson(['status' => 'down']);
    }

    #[Test]
    public function it_is_down_when_the_scheduler_has_gone_quiet(): void
    {
        $this->recordSchedulerRun('last_started_at', now()->subHours(3));

        $this->getJson('/up')->assertStatus(500)->assertJson(['status' => 'down']);
    }

    #[Test]
    public function skipped_runs_still_count_as_the_scheduler_running(): void
    {
        $this->recordSchedulerRun('last_skipped_at', now()->subMinutes(5));

        $this->getJson('/up')->assertOk();
    }

    #[Test]
    public function a_fresh_install_without_scheduler_history_is_up(): void
    {
        $this->getJson('/up')->assertOk();
    }
}
