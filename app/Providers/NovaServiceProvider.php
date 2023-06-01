<?php

namespace App\Providers;

use App\Models\Observers\TicketTypeObserver;
use App\Models\TicketType;
use App\Models\User;
use App\Nova\Metrics\MembersByMonth;
use App\Nova\Metrics\UsersByRole;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        parent::boot();

        Nova::serving(function () {
            TicketType::observe(TicketTypeObserver::class);
        });
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [];
    }

    /**
     * Register any application services.
     */
    public function register()
    {
    }

    /**
     * Register the Nova routes.
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes(['web', \App\Http\Middleware\MustBeAdmin::class])
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     */
    protected function gate()
    {
        Gate::define('viewNova', fn (User $user) => $user->isRole('administrator'));
    }

    /**
     * Get the cards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            new MembersByMonth(),
            new UsersByRole(),
        ];
    }
}
