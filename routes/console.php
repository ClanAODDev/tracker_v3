<?php

use App\Console\Commands\DivisionCensus;
use App\Console\Commands\FetchApplicationFeeds;
use App\Console\Commands\MemberSync;
use App\Jobs\CleanupUnassignedLeaders;
use App\Jobs\PartTimeMemberCleanup;
use App\Jobs\PurgePendingDiscordRegistrations;
use App\Jobs\ResetOrphanedUnitAssignments;
use Illuminate\Support\Facades\Schedule;

Schedule::command(FetchApplicationFeeds::class, ['--notify'])->everyFiveMinutes();
Schedule::command(MemberSync::class)->hourly();
Schedule::command(DivisionCensus::class)->weekly();

/**
 * Clean up tasks
 */
Schedule::job(new ResetOrphanedUnitAssignments)->weekly();
Schedule::job(new CleanupUnassignedLeaders)->weekly();
Schedule::job(new PartTimeMemberCleanup)->weekly();
Schedule::job(new PurgePendingDiscordRegistrations)->daily();
