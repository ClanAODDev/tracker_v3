<?php

namespace App\Providers;

use App\Squad;
use App\Member;
use App\Platoon;
use App\Division;

use App\Policies\SquadPolicy;
use App\Policies\MemberPolicy;
use App\Policies\PlatoonPolicy;
use App\Policies\DivisionPolicy;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Member::class => MemberPolicy::class,
        Squad::class => SquadPolicy::class,
        Platoon::class => PlatoonPolicy::class,
        Division::class => DivisionPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Passport::tokensCan([
            'provision' => 'Provision Test',
        ]);
    }
}
