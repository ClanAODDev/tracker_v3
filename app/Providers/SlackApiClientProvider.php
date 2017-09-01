<?php

namespace App\Providers;

use CL\Slack\Transport\ApiClient;
use Illuminate\Support\ServiceProvider;

class SlackApiClientProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ApiClient::class, function ($app) {
            return new ApiClient(config('services.slack.token'));
        });
    }
}
