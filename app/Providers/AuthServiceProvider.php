<?php

namespace App\Providers;

use App\Division;
use App\Member;
use App\MemberRequest;
use App\Note;
use App\Platoon;
use App\Policies\DivisionPolicy;
use App\Policies\MemberPolicy;
use App\Policies\MemberRequestPolicy;
use App\Policies\NotePolicy;
use App\Policies\PlatoonPolicy;
use App\Policies\SquadPolicy;
use App\Policies\UserPolicy;
use App\Squad;
use App\User;
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
        Note::class => NotePolicy::class,
        User::class => UserPolicy::class,
        MemberRequest::class => MemberRequestPolicy::class,
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
