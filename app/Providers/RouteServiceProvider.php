<?php

namespace App\Providers;

use App\Squad;
use App\Member;
use App\Platoon;
use App\Division;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

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
            $model = Division::whereAbbreviation(strtolower($division))->first();
            if ($model instanceof Division) {
                return $model;
            }
        });

        /**
         * Show platoon by division abbrev, platoon number (1st, 2nd, etc)
         */
        \Route::bind('platoon', function ($platoon) {
            $model = Platoon::whereId($platoon)->first();
            if ($model instanceof Platoon) {
                return $model;
            }
        });


        \Route::bind('squad', function ($squad) {
            $model = Squad::whereId($squad)->first();
            if ($model instanceof Squad) {
                return $model;
            }
        });

        /**
         * Show member by clan member id (forum id)
         */
        \Route::bind('member', function ($member) {
            $model = Member::whereClanId($member)->first();
            if ($model instanceof Member) {
                return $model;
            }
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


