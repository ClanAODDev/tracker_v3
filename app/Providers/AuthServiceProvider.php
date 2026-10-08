<?php

namespace App\Providers;

use App\Authorization\AbilityMap;
use App\Authorization\DatabaseAbilityMap;
use App\Authorization\ForumRoleSource;
use App\Authorization\RoleSource;
use App\Authorization\UnitHierarchy;
use App\Authorization\UnitTreeHierarchy;
use App\Enums\Ability;
use App\Models\Division;
use App\Models\DivisionMemberField;
use App\Models\DivisionTag;
use App\Models\Leave;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Note;
use App\Models\RankAction;
use App\Models\Ticket;
use App\Models\Unit;
use App\Models\User;
use App\Policies\ApiTokenPolicy;
use App\Policies\DivisionMemberFieldPolicy;
use App\Policies\DivisionPolicy;
use App\Policies\DivisionTagPolicy;
use App\Policies\LeavePolicy;
use App\Policies\MemberPolicy;
use App\Policies\MemberRequestPolicy;
use App\Policies\NotePolicy;
use App\Policies\RankActionPolicy;
use App\Policies\TicketPolicy;
use App\Policies\UnitPolicy;
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
        Division::class            => DivisionPolicy::class,
        DivisionMemberField::class => DivisionMemberFieldPolicy::class,
        DivisionTag::class         => DivisionTagPolicy::class,
        Leave::class               => LeavePolicy::class,
        Member::class              => MemberPolicy::class,
        MemberRequest::class       => MemberRequestPolicy::class,
        NewAccessToken::class      => ApiTokenPolicy::class,
        Note::class                => NotePolicy::class,
        RankAction::class          => RankActionPolicy::class,
        Ticket::class              => TicketPolicy::class,
        Unit::class                => UnitPolicy::class,
        User::class                => UserPolicy::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(RoleSource::class, ForumRoleSource::class);
        $this->app->scoped(AbilityMap::class, DatabaseAbilityMap::class);
        $this->app->scoped(UnitHierarchy::class, UnitTreeHierarchy::class);
    }

    /**
     * Register any application authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        foreach (Ability::cases() as $ability) {
            Gate::define($ability, fn (User $user) => $this->app->make(AbilityMap::class)->allows($user, $ability));
        }
    }
}
