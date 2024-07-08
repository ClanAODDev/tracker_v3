<?php

namespace App\Providers;

use App\Models\Observers\TicketTypeObserver;
use App\Models\TicketType;
use App\Settings\UserSettings;
use Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Schema;
use wrapi\slack\slack;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        TicketType::observe(TicketTypeObserver::class);

        Paginator::useBootstrap();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // register user settings
        $this->app->singleton(UserSettings::class, fn () => Auth::user()->settings());

        // register slack api client
        $this->app->singleton(slack::class, fn () => new slack(config('core.slack.token')));
    }
}
