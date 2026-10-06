<?php

namespace Tests\Feature\Filament;

use App\Enums\Ability;
use App\Enums\Role;
use App\Filament\Admin\Pages\RolePermissions;
use App\Models\AbilityAudit;
use App\Models\RoleAbility;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RolePermissionsPageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
        $this->admin = User::factory()->admin()->create();
    }

    #[Test]
    public function only_users_who_can_manage_permissions_can_open_it(): void
    {
        $this->actingAs($this->admin)->get(RolePermissions::getUrl())->assertOk();
        $this->actingAs(User::factory()->create(['role' => Role::SENIOR_LEADER]))->get(RolePermissions::getUrl())->assertForbidden();
    }

    #[Test]
    public function it_loads_the_selected_roles_current_abilities(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(RolePermissions::class)
            ->assertSet('data.role', Role::OFFICER->value)
            ->assertSet('data.abilities.access', fn (array $values) => in_array(Ability::AccessModPanel->value, $values, true)
                && ! in_array(Ability::AccessAdminPanel->value, $values, true))
            ->fillForm(['role' => Role::ADMIN->value])
            ->assertSet('data.abilities.access', fn (array $values) => in_array(Ability::AccessAdminPanel->value, $values, true));
    }

    #[Test]
    public function saving_requires_a_reason_and_records_the_changes(): void
    {
        $this->actingAs($this->admin);

        $page    = Livewire::test(RolePermissions::class);
        $members = $page->get('data.abilities.members');
        $members = array_values(array_diff($members, [Ability::Recruit->value]));

        $page->fillForm(['abilities.members' => $members])
            ->callAction('save', data: ['reason' => ''])
            ->assertHasActionErrors(['reason' => 'required']);

        $this->assertTrue(RoleAbility::where('role', Role::OFFICER)->where('ability', Ability::Recruit->value)->exists());

        Livewire::test(RolePermissions::class)
            ->fillForm(['abilities.members' => $members])
            ->callAction('save', data: ['reason' => 'Recruiting moves to senior leaders'])
            ->assertHasNoActionErrors();

        $this->assertFalse(RoleAbility::where('role', Role::OFFICER)->where('ability', Ability::Recruit->value)->exists());
        $this->assertSame(1, AbilityAudit::where('reason', 'Recruiting moves to senior leaders')->count());
    }

    #[Test]
    public function resetting_restores_the_role_defaults(): void
    {
        $this->actingAs($this->admin);
        RoleAbility::where('role', Role::OFFICER)->delete();

        Livewire::test(RolePermissions::class)
            ->callAction('reset', data: ['reason' => 'Undo experiment'])
            ->assertHasNoActionErrors()
            ->assertSet('data.abilities.access', fn (array $values) => in_array(Ability::AccessModPanel->value, $values, true));

        $this->assertTrue(RoleAbility::where('role', Role::OFFICER)->where('ability', Ability::AccessModPanel->value)->exists());
    }
}
