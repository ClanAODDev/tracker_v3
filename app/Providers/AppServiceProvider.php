<?php

namespace App\Providers;

use App\Models\TicketType;
use App\Models\Observers\TicketTypeObserver;
use App\Settings\UserSettings;
use Auth;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Schema;
use wrapi\slack\slack;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        TicketType::observe(TicketTypeObserver::class);

        Paginator::useBootstrap();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        // register user settings
        $this->app->singleton(UserSettings::class, fn() => Auth::user()->settings());

        // register slack api client
        $this->app->singleton(slack::class, fn() => new slack(config('core.slack.token')));
    }
}
