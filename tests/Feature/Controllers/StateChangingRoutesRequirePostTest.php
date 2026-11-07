<?php

namespace Tests\Feature\Controllers;

use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class StateChangingRoutesRequirePostTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function state_changing_routes_refuse_get(): void
    {
        $admin   = $this->createAdmin();
        $officer = $this->createOfficer();

        $this->actingAs($admin);

        $this->get('/logout')->assertMethodNotAllowed();
        $this->get('/impersonate/' . $officer->id)->assertMethodNotAllowed();
        $this->get('/impersonate-end')->assertMethodNotAllowed();
        $this->get('/impersonate-role/officer')->assertMethodNotAllowed();
        $this->get('/impersonate-role-end')->assertMethodNotAllowed();
        $this->get('/inactive-members/' . $officer->member->clan_id . '/flag-inactive')->assertMethodNotAllowed();
        $this->get('/inactive-members/' . $officer->member->clan_id . '/unflag-inactive')->assertMethodNotAllowed();

        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session('impersonating'));
    }

    #[Test]
    public function the_admin_user_table_action_impersonates(): void
    {
        Filament::setCurrentPanel('admin');

        $admin   = $this->createAdmin();
        $officer = $this->createOfficer();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->callTableAction('impersonate', $officer)
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($officer);
        $this->assertTrue(session('impersonating'));
    }
}
