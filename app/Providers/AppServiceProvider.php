<?php

namespace App\Providers;

use App\Settings\UserSettings;
use Auth;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use CL\Slack\Transport\ApiClient;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;
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
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() == 'local') {
            $this->app->register('Laracasts\Generators\GeneratorsServiceProvider');
        }

        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        // register user settings
        $this->app->singleton(UserSettings::class, function () {
            return Auth::user()->settings();
        });

        // register slack api client
        $this->app->singleton(slack::class, function () {
            return new slack(config('core.slack.token'));
        });
    }
}
