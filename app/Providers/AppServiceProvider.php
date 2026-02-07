<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\Observers\TicketTypeObserver;
use App\Models\TicketType;
use App\Settings\UserSettings;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Discord\DiscordExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

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

        Event::listen(SocialiteWasCalled::class, DiscordExtendSocialite::class . '@handle');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (! class_exists(\Filament\Tables\Actions\Action::class)) {
            class_alias(\Filament\Actions\Action::class, \Filament\Tables\Actions\Action::class);
        }

        $this->app->singleton(UserSettings::class, fn () => Auth::user()->settings());

        $this->app->bind(\Filament\Auth\Http\Responses\Contracts\LogoutResponse::class, LogoutResponse::class);

        if ($this->app->environment('local', 'testing')) {
            $this->app->register(FakerServiceProvider::class);
        }
    }
}
