<?php

namespace App\Providers;

use Auth;
use CL\Slack\Transport\ApiClient;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
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

        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }

        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        // register user settings
        $this->app->singleton(\App\Settings\UserSettings::class, function () {
            return Auth::user()->settings();
        });

        // register slack api client
        $this->app->singleton(ApiClient::class, function () {
            return new ApiClient(config('services.slack.token'));
        });
    }
}
