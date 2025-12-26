<?php

use App\Console\Commands\DivisionCensus;
use App\Console\Commands\FetchApplicationFeeds;
use App\Console\Commands\MemberSync;
use Illuminate\Support\Facades\Schedule;

Schedule::command(FetchApplicationFeeds::class, ['--notify'])->everyFiveMinutes();
Schedule::command(MemberSync::class)->hourly();
Schedule::command(DivisionCensus::class)->weekly();
