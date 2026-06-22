<?php

use App\Console\Commands\DivisionCensus;
use App\Console\Commands\FetchApplicationFeeds;
use App\Console\Commands\MemberSync;
use App\Console\Commands\NotifyMilestoneAwards;
use App\Jobs\CleanupUnassignedLeaders;
use App\Jobs\PartTimeMemberCleanup;
use App\Jobs\PurgePendingDiscordRegistrations;
use App\Jobs\ResetOrphanedUnitAssignments;
use App\Jobs\SyncDivisionDns;
use Illuminate\Support\Facades\Schedule;

Schedule::command(FetchApplicationFeeds::class, ['--notify'])->everyFiveMinutes()
    ->description('Poll AOD forum application feeds and send Discord notifications');

Schedule::command(MemberSync::class)->hourly()
    ->description('Sync member data from AOD forums');

Schedule::command(DivisionCensus::class)->weekly()
    ->description('Record weekly division population snapshot');

Schedule::command(NotifyMilestoneAwards::class)->lastDayOfMonth('08:00')
    ->description('Send Discord notifications for milestone award recipients');

Schedule::job(new ResetOrphanedUnitAssignments)->weekly()
    ->monitorName('reset-orphaned-unit-assignments')
    ->description('Clear platoon/squad assignments for members with no matching unit');

Schedule::job(new CleanupUnassignedLeaders)->weekly()
    ->monitorName('cleanup-unassigned-leaders')
    ->description('Remove leader positions from members no longer assigned to a unit');

Schedule::job(new PartTimeMemberCleanup)->weekly()
    ->monitorName('part-time-member-cleanup')
    ->description('Remove stale part-time division assignments');

Schedule::job(new PurgePendingDiscordRegistrations)->daily()
    ->monitorName('purge-pending-discord-registrations')
    ->description('Delete pending Discord registrations older than 60 days');

Schedule::job(new SyncDivisionDns)->daily()
    ->monitorName('sync-division-dns')
    ->description('Sync active division CNAMEs with Cloudflare DNS');
