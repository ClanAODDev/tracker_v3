<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\Division;
use App\Models\Member;
use App\Models\Observers\TicketTypeObserver;
use App\Models\TicketType;
use App\Models\User;
use App\Settings\UserSettings;
use Filament\Actions\Action;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Discord\DiscordExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        TicketType::observe(TicketTypeObserver::class);

        Paginator::useBootstrap();

        Event::listen(SocialiteWasCalled::class, DiscordExtendSocialite::class . '@handle');

        Route::bind('division', function ($division) {
            return Division::where('slug', strtolower($division))->first();
        });

        Route::bind('username', fn ($username) => User::whereName($username)->firstOrFail());

        Route::bind('member', fn ($member) => Member::whereClanId($member)->firstOrFail());
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (! class_exists(\Filament\Tables\Actions\Action::class)) {
            class_alias(Action::class, \Filament\Tables\Actions\Action::class);
        }

        $this->app->singleton(UserSettings::class, fn () => auth()->user()->settings());

        $this->app->bind(\Filament\Auth\Http\Responses\Contracts\LogoutResponse::class, LogoutResponse::class);

        if ($this->app->environment('local', 'testing')) {
            $this->app->register(FakerServiceProvider::class);
        }
    }
}
