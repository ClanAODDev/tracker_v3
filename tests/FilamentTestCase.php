<?php

namespace Tests;

use App\Enums\Role;
use App\Models\Division;
use App\Models\Member;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

abstract class FilamentTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createAdminUser(): User
    {
        return User::factory()->admin()->create();
    }

    protected function createOfficerUser(): User
    {
        return User::factory()->officer()->create();
    }

    protected function createModUser(): User
    {
        return User::factory()->state([
            'role' => Role::SENIOR_LEADER,
        ])->create();
    }

    protected function createMemberUser(): User
    {
        return User::factory()->create();
    }

    protected function actingAsAdmin(): static
    {
        $this->actingAs($this->createAdminUser());

        return $this;
    }

    protected function actingAsOfficer(): static
    {
        $this->actingAs($this->createOfficerUser());

        return $this;
    }

    protected function actingAsMod(): static
    {
        $this->actingAs($this->createModUser());

        return $this;
    }

    protected function actingAsMember(): static
    {
        $this->actingAs($this->createMemberUser());

        return $this;
    }

    protected function setAdminPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function setModPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('mod'));
    }

    protected function setProfilePanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('profile'));
    }

    protected function createDivisionWithStructure(): array
    {
        $division = Division::factory()->create();
        $member = Member::factory()->create([
            'division_id' => $division->id,
        ]);

        return [
            'division' => $division,
            'member' => $member,
        ];
    }

    protected function assertCanAccessResource(string $resourceClass): void
    {
        $resource = $resourceClass::getUrl('index');
        $this->get($resource)->assertSuccessful();
    }

    protected function assertCannotAccessResource(string $resourceClass): void
    {
        $resource = $resourceClass::getUrl('index');
        $this->get($resource)->assertForbidden();
    }

    protected function assertCanRenderPage(string $pageClass): void
    {
        Livewire::test($pageClass)->assertSuccessful();
    }
}
