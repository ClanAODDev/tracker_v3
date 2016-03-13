<?php

namespace App\Providers;

use App\Member;
use App\Platoon;
use App\Division;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);

        /**
         * Show division by abbreviation
         */
        \Route::bind('division', function ($division) {
            return Division::where('abbreviation', strtolower($division))->firstOrFail();
        });

        /**
         * Show member by clan member id (forum id)
         */
        \Route::bind('member', function ($member) {
            return Member::where('clan_id', $member)->firstOrFail();
        });

        /**
         * Show platoon by division abbrev, platoon number (1st, 2nd, etc)
         */
        \Route::bind('platoon', function ($division, $platoon) {
            return Platoon::where([
                'abbreviation' => strtolower($division),
                'number' => $platoon
            ])->firstOrFail();
        });

    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router) {
            require app_path('Http/routes.php');
        });
    }
}
