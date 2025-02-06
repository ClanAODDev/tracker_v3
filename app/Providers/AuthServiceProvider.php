<?php

namespace App\Providers;

use App\Models\Division;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Note;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\ApiTokenPolicy;
use App\Policies\DivisionPolicy;
use App\Policies\MemberPolicy;
use App\Policies\MemberRequestPolicy;
use App\Policies\NotePolicy;
use App\Policies\PlatoonPolicy;
use App\Policies\SquadPolicy;
use App\Policies\TicketPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\NewAccessToken;

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
        Ticket::class => TicketPolicy::class,
        NewAccessToken::class => ApiTokenPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('viewLogViewer', function (?User $user) {
            if (auth()->check()) {
                return $user->isRole('admin');
            }
            
            return false;
        });
    }
}
