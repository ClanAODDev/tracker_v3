<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\Observers\TicketTypeObserver;
use App\Models\TicketType;
use App\Settings\UserSettings;
use Auth;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Schema;

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
        $this->app->singleton(UserSettings::class, fn () => Auth::user()->settings());

        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
    }
}
