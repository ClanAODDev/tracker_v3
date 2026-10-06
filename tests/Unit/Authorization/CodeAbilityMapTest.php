<?php

namespace Tests\Unit\Authorization;

use App\Authorization\AbilityMap;
use App\Enums\Ability;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CodeAbilityMapTest extends TestCase
{
    #[Test]
    public function every_ability_reproduces_todays_role_checks_for_every_role_and_impersonation(): void
    {
        $sites = $this->todaysChecks();

        $this->assertEqualsCanonicalizing(
            array_map(fn (Ability $ability) => $ability->value, Ability::cases()),
            array_keys($sites),
        );

        $map = $this->app->make(AbilityMap::class);

        foreach ($this->users() as $label => [$user, $impersonating]) {
            $this->impersonate($impersonating);

            foreach ($sites as $value => [$today, $shortcut]) {
                $ability = Ability::from($value);
                $guard   = $shortcut ?? fn () => false;

                $this->assertSame(
                    $guard($user) || $today($user),
                    $guard($user) || $map->allows($user, $ability),
                    "{$ability->name} differs for {$label}",
                );
            }
        }
    }

    #[Test]
    public function stored_role_abilities_ignore_role_impersonation(): void
    {
        $map  = $this->app->make(AbilityMap::class);
        $user = $this->user(Role::ADMIN);

        $this->impersonate(Role::MEMBER);

        $this->assertFalse($user->isRole('admin'));
        $this->assertTrue($map->allows($user, Ability::Recruit));
        $this->assertTrue($map->allows($user, Ability::ConductTraining));
        $this->assertTrue($map->allows($user, Ability::UseAdvancedApiScopes));
        $this->assertFalse($map->allows($user, Ability::ManageAllMembers));
    }

    #[Test]
    public function the_gate_answers_every_ability_from_the_map(): void
    {
        $map = $this->app->make(AbilityMap::class);

        foreach ($this->users() as $label => [$user, $impersonating]) {
            $this->impersonate($impersonating);

            foreach (Ability::cases() as $ability) {
                $this->assertSame(
                    $map->allows($user, $ability),
                    Gate::forUser($user)->allows($ability),
                    "{$ability->name} gate differs for {$label}",
                );
            }
        }
    }

    #[Test]
    public function the_gate_resolves_the_map_through_its_interface(): void
    {
        $this->app->instance(AbilityMap::class, new class implements AbilityMap
        {
            public function allows(User $user, Ability $ability): bool
            {
                return $ability === Ability::ManageGlobalTags;
            }
        });

        $user = $this->user(Role::MEMBER);

        $this->assertTrue(Gate::forUser($user)->allows(Ability::ManageGlobalTags));
        $this->assertFalse(Gate::forUser($user)->allows(Ability::AccessModPanel));
    }

    #[Test]
    public function every_ability_is_described_for_the_permissions_screen(): void
    {
        foreach (Ability::cases() as $ability) {
            $this->assertNotSame('', $ability->label(), $ability->name);
            $this->assertNotSame('', $ability->area(), $ability->name);
            $this->assertNotSame('', $ability->description(), $ability->name);
        }
    }

    #[Test]
    public function guests_hold_no_abilities(): void
    {
        foreach (Ability::cases() as $ability) {
            $this->assertFalse(Gate::allows($ability), $ability->name);
        }
    }

    private function todaysChecks(): array
    {
        $is     = fn (string|array|Role $roles) => fn (User $user) => $user->isRole($roles);
        $isNot  = fn (string|array|Role $roles) => fn (User $user) => ! $user->isRole($roles);
        $stored = fn (array $roles) => fn (User $user) => in_array($user->role, $roles, true);
        $admin  = $is('admin');
        $checks = [
            Ability::AccessModPanel->value              => [$is(['admin', 'sr_ldr', 'officer']), null],
            Ability::AccessAdminPanel->value            => [$is('admin'), null],
            Ability::ViewAdminPages->value              => [$is('admin'), null],
            Ability::UseHelpDesk->value                 => [$is(['admin', 'sr_ldr', 'officer']), null],
            Ability::ViewRecruitingGuide->value         => [$is(['admin', 'sr_ldr', 'officer']), null],
            Ability::PreviewApplicationForm->value      => [$is(['admin', 'sr_ldr', 'officer']), null],
            Ability::UseBulkMode->value                 => [$is(['officer', 'sr_ldr', 'admin']), null],
            Ability::ImpersonateRoles->value            => [$is('admin'), null],
            Ability::ImpersonateUsers->value            => [$is('admin'), null],
            Ability::ManageUsers->value                 => [$is('admin'), null],
            Ability::ManageApiTokens->value             => [$is('admin'), null],
            Ability::UseAdvancedApiScopes->value        => [$stored([Role::SENIOR_LEADER, Role::ADMIN]), null],
            Ability::ManageDivisions->value             => [$is('admin'), null],
            Ability::ViewDivisionSettings->value        => [$is('sr_ldr'), $admin],
            Ability::SeeDivisionHealthAlerts->value     => [$is('sr_ldr'), null],
            Ability::ActAcrossDivisions->value          => [$is('admin'), null],
            Ability::ManageAllUnits->value              => [$is(['admin']), null],
            Ability::ViewUnits->value                   => [$is(['officer', 'sr_ldr']), $admin],
            Ability::ManageUnits->value                 => [$is('sr_ldr'), $admin],
            Ability::ManageAllMembers->value            => [$is('admin'), null],
            Ability::Recruit->value                     => [fn (User $user) => $user->role->value > Role::MEMBER->value, $admin],
            Ability::ManageMembers->value               => [$is('sr_ldr'), $admin],
            Ability::ManageDivisionMembers->value       => [$is('officer'), $is(['admin', 'sr_ldr'])],
            Ability::RemindInactiveMembers->value       => [$is(['officer', 'sr_ldr']), $admin],
            Ability::ManagePartTimers->value            => [$is(['officer', 'sr_ldr']), $admin],
            Ability::ClearActivityReminders->value      => [$is(['sr_ldr', 'admin']), null],
            Ability::SeparateMembers->value             => [$is('sr_ldr'), $admin],
            Ability::PromoteMembers->value              => [$is('officer'), $admin],
            Ability::ViewMemberHistory->value           => [$isNot('member'), null],
            Ability::ViewDivisionActivity->value        => [$isNot('member'), null],
            Ability::ViewAllActivity->value             => [$is(['sr_ldr', 'admin']), null],
            Ability::ManageUnassignedMembers->value     => [$is('sr_ldr'), $admin],
            Ability::ConductTraining->value             => [$stored([Role::ADMIN, Role::SENIOR_LEADER]), null],
            Ability::ManageAllTransfers->value          => [$is('admin'), null],
            Ability::ViewTransfers->value               => [$is(['officer', 'sr_ldr']), $admin],
            Ability::TransferMembers->value             => [$is(['admin', 'sr_ldr']), null],
            Ability::ManageAllMemberRequests->value     => [$is('admin'), null],
            Ability::ManageMemberRequests->value        => [$is(['sr_ldr', 'admin']), null],
            Ability::DeleteApplications->value          => [$is(['sr_ldr', 'admin']), null],
            Ability::ViewLeaves->value                  => [$isNot('member'), null],
            Ability::CreateLeaves->value                => [$isNot('member'), null],
            Ability::EditLeaves->value                  => [$is(['admin', 'sr_ldr']), null],
            Ability::ManageAllNotes->value              => [$is('admin'), null],
            Ability::ViewNotes->value                   => [$isNot('member'), $admin],
            Ability::CreateNotes->value                 => [$isNot('member'), $admin],
            Ability::EditAnyNote->value                 => [$is(Role::SENIOR_LEADER), $admin],
            Ability::UseSeniorLeaderNotes->value        => [$is(['admin', 'sr_ldr']), null],
            Ability::UseMasterSergeantNotes->value      => [$is('admin'), null],
            Ability::ViewRankActions->value             => [$is(['officer', 'sr_ldr', 'admin']), null],
            Ability::ManageAllRankActions->value        => [$is('admin'), null],
            Ability::OverrideRankActionRules->value     => [$is('admin'), null],
            Ability::DemoteMembers->value               => [$is('admin'), null],
            Ability::AutoApproveJuniorPromotions->value => [$is('admin'), null],
            Ability::ManageMemberAwards->value          => [$is(['admin', 'sr_ldr']), null],
            Ability::ApproveAwards->value               => [$is(['admin', 'sr_ldr']), null],
            Ability::RequestAwards->value               => [$is(['officer', 'sr_ldr', 'admin']), null],
            Ability::ManageGlobalTags->value            => [$is('admin'), null],
            Ability::ManageDivisionTags->value          => [$is('sr_ldr'), $admin],
            Ability::AssignTags->value                  => [$is([Role::OFFICER, Role::SENIOR_LEADER]), $admin],
            Ability::UseSeniorLeaderTags->value         => [$is(['admin', 'sr_ldr']), null],
            Ability::UseOfficerTags->value              => [$is('officer'), $is(['admin', 'sr_ldr'])],
            Ability::ManageAllTickets->value            => [$is('admin'), null],
            Ability::ManagePermissions->value           => [$is('admin'), null],
        ];

        return $checks;
    }

    private function users(): array
    {
        $users = [];

        foreach (Role::cases() as $stored) {
            $users["{$stored->slug()}"] = [$this->user($stored), null];

            foreach (Role::cases() as $impersonated) {
                $users["{$stored->slug()} viewing as {$impersonated->slug()}"] = [$this->user($stored), $impersonated];
            }
        }

        return $users;
    }

    private function user(Role $role): User
    {
        return (new User)->forceFill(['role' => $role]);
    }

    private function impersonate(?Role $role): void
    {
        session()->forget('impersonatingRole');

        if ($role) {
            session(['impersonatingRole' => $role->value]);
        }
    }
}
