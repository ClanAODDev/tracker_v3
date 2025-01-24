<?php

namespace App\Console;

use App\Console\Commands\DivisionCensus;
use App\Console\Commands\FetchApplicationFeeds;
use App\Console\Commands\MemberSync;
use App\Console\Commands\PartTimeMemberCleanup;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\MemberSync::class,
        Commands\DivisionCensus::class,
        Commands\MakeAODToken::class,
        Commands\SgtActivity::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(FetchApplicationFeeds::class)->everyTenMinutes();
        $schedule->command(MemberSync::class)->hourly();
        $schedule->command(DivisionCensus::class)->weekly();
        $schedule->command(PartTimeMemberCleanup::class)->weekly();
    }

    /**
     * Register the Closure based commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
