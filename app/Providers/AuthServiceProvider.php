<?php

namespace App\Providers;

use App\Models\Division;
use App\Models\DivisionTag;
use App\Models\Leave;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Note;
use App\Models\Platoon;
use App\Models\RankAction;
use App\Models\Squad;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\ApiTokenPolicy;
use App\Policies\DivisionPolicy;
use App\Policies\DivisionTagPolicy;
use App\Policies\LeavePolicy;
use App\Policies\MemberPolicy;
use App\Policies\MemberRequestPolicy;
use App\Policies\NotePolicy;
use App\Policies\PlatoonPolicy;
use App\Policies\RankActionPolicy;
use App\Policies\SquadPolicy;
use App\Policies\TicketPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Sanctum\NewAccessToken;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Division::class => DivisionPolicy::class,
        DivisionTag::class => DivisionTagPolicy::class,
        Leave::class => LeavePolicy::class,
        Member::class => MemberPolicy::class,
        MemberRequest::class => MemberRequestPolicy::class,
        NewAccessToken::class => ApiTokenPolicy::class,
        Note::class => NotePolicy::class,
        Platoon::class => PlatoonPolicy::class,
        RankAction::class => RankActionPolicy::class,
        Squad::class => SquadPolicy::class,
        Ticket::class => TicketPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
