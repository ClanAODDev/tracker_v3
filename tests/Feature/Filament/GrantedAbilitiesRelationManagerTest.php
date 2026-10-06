<?php

namespace Tests\Feature\Filament;

use App\Enums\Ability;
use App\Enums\Role;
use App\Filament\Admin\Resources\UserResource\Pages\EditUser;
use App\Filament\Admin\Resources\UserResource\RelationManagers\GrantedAbilitiesRelationManager;
use App\Models\AbilityAudit;
use App\Models\User;
use App\Models\UserAbility;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GrantedAbilitiesRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $target;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
        $this->admin  = User::factory()->admin()->create();
        $this->target = User::factory()->create(['role' => Role::MEMBER]);
    }

    #[Test]
    public function only_users_who_can_manage_permissions_see_the_tab(): void
    {
        $this->actingAs($this->admin);
        $this->assertTrue(GrantedAbilitiesRelationManager::canViewForRecord($this->target, EditUser::class));

        $this->actingAs(User::factory()->create(['role' => Role::SENIOR_LEADER]));
        $this->assertFalse(GrantedAbilitiesRelationManager::canViewForRecord($this->target, EditUser::class));
    }

    #[Test]
    public function granting_an_ability_requires_a_reason_and_takes_effect(): void
    {
        $this->actingAs($this->admin);

        $this->relationManager()
            ->callTableAction('grant', data: ['ability' => Ability::Recruit->value, 'reason' => ''])
            ->assertHasTableActionErrors(['reason' => 'required']);

        $this->relationManager()
            ->callTableAction('grant', data: ['ability' => Ability::Recruit->value, 'reason' => 'Recruitment drive'])
            ->assertHasNoTableActionErrors();

        $this->assertTrue(Gate::forUser($this->target)->allows(Ability::Recruit));
        $this->assertSame($this->admin->id, UserAbility::sole()->granted_by);
        $this->assertSame('Recruitment drive', AbilityAudit::sole()->reason);
    }

    #[Test]
    public function revoking_a_grant_removes_it_and_is_audited(): void
    {
        $this->actingAs($this->admin);
        $grant = UserAbility::create(['user_id' => $this->target->id, 'ability' => Ability::Recruit->value, 'granted_by' => $this->admin->id, 'reason' => 'drive']);

        $this->relationManager()
            ->callTableAction('revoke', $grant, data: ['reason' => 'Drive over'])
            ->assertHasNoTableActionErrors();

        $this->assertSame(0, UserAbility::count());
        $this->assertFalse(Gate::forUser($this->target->fresh())->allows(Ability::Recruit));
        $this->assertSame('Drive over', AbilityAudit::sole()->reason);
    }

    private function relationManager()
    {
        return Livewire::test(GrantedAbilitiesRelationManager::class, [
            'ownerRecord' => $this->target,
            'pageClass'   => EditUser::class,
        ]);
    }
}
