<?php

namespace Tests\Feature\Filament;

use App\Authorization\PermissionManager;
use App\Enums\Ability;
use App\Enums\Role;
use App\Filament\Admin\Resources\AbilityAuditResource;
use App\Filament\Admin\Resources\AbilityAuditResource\Pages\ListAbilityAudits;
use App\Models\AbilityAudit;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AbilityAuditResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
    }

    #[Test]
    public function admins_see_every_permission_change(): void
    {
        $admin  = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => Role::MEMBER]);
        $this->actingAs($admin);

        app(PermissionManager::class)->grant($member, Ability::Recruit, $admin, 'Recruitment drive');
        app(PermissionManager::class)->setRoleAbilities(Role::BANNED, [], $admin, 'Banned users get nothing');

        $this->get(AbilityAuditResource::getUrl())->assertOk();

        Livewire::test(ListAbilityAudits::class)
            ->assertCanSeeTableRecords(AbilityAudit::all())
            ->assertSee('Recruitment drive')
            ->assertSee('Banned users get nothing');
    }

    #[Test]
    public function others_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create(['role' => Role::SENIOR_LEADER]))
            ->get(AbilityAuditResource::getUrl())
            ->assertForbidden();
    }
}
