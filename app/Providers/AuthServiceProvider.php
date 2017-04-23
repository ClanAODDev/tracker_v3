<?php

namespace App\Providers;


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
        \App\Member::class => \App\Policies\MemberPolicy::class,
        \App\Squad::class => \App\Policies\SquadPolicy::class,
        \App\Platoon::class => \App\Policies\PlatoonPolicy::class,
        \App\Division::class => \App\Policies\DivisionPolicy::class,
        \App\Note::class => \App\Policies\NotePolicy::class,
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
            'query-division-info-basic' => 'Query basic information about divisions',
            'query-division-info-full' => 'Query full information about divisions',
            'query-member-data' => 'Query member data',
        ]);
    }
}
