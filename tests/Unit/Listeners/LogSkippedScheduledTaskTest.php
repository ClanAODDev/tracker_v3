<?php

namespace Tests\Unit\Listeners;

use App\Listeners\LogSkippedScheduledTask;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\ScheduledTaskSkipReason;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogSkippedScheduledTaskTest extends TestCase
{
    #[Test]
    public function it_is_registered_for_skipped_scheduled_tasks()
    {
        Event::fake();

        Event::assertListening(ScheduledTaskSkipped::class, LogSkippedScheduledTask::class);
    }

    #[Test]
    public function it_logs_the_task_and_skip_reason()
    {
        Log::spy();

        $task = app(Schedule::class)->command('tracker:member-sync');

        (new LogSkippedScheduledTask)->handle(new ScheduledTaskSkipped($task, ScheduledTaskSkipReason::ConditionNotMet));

        Log::shouldHaveReceived('info')->once()->with('Scheduled task skipped', [
            'task'   => $task->getSummaryForDisplay(),
            'reason' => 'condition_not_met',
        ]);
    }
}
