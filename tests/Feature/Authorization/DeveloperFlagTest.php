<?php

namespace Tests\Feature\Authorization;

use App\Enums\Ability;
use App\Enums\Role;
use App\Models\Award;
use App\Models\Division;
use App\Models\User;
use App\Models\UserAbility;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DeveloperFlagTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function developerOfficer(): User
    {
        $division = $this->createActiveDivision();

        return $this->createMemberWithUser(['division_id' => $division->id], ['role' => Role::OFFICER, 'developer' => true]);
    }

    #[Test]
    public function the_developer_flag_alone_grants_nothing_extra(): void
    {
        $developer = $this->developerOfficer();
        $this->actingAs($developer);

        $this->assertFalse($developer->can(Ability::ViewLogs));
        $this->assertFalse($developer->can('deleteAny', Division::class));
        $this->assertFalse($developer->can('deleteAny', Award::class));
        $this->assertFalse($developer->can('viewAny', User::class));
        $this->assertFalse($developer->canAccessPanel(Filament::getPanel('admin')));

        $this->get('/log-viewer')->assertForbidden();
    }

    #[Test]
    public function the_new_abilities_work_when_granted(): void
    {
        $user = $this->developerOfficer();

        foreach ([Ability::ViewLogs, Ability::DeleteDivisions, Ability::DeleteAwards] as $ability) {
            UserAbility::create(['user_id' => $user->id, 'ability' => $ability->value, 'reason' => 'test']);
        }

        $this->actingAs($user);

        $this->assertTrue($user->can('deleteAny', Division::class));
        $this->assertTrue($user->can('deleteAny', Award::class));
        $this->get('/log-viewer')->assertOk();
    }

    #[Test]
    public function the_menu_follows_view_logs_rather_than_the_developer_flag(): void
    {
        $this->actingAs($this->developerOfficer())
            ->get(route('home'))
            ->assertInertia(fn ($page) => $page
                ->where('auth.permissions.canViewAsRole', false)
                ->where('nav', function ($nav) {
                    $this->assertStringNotContainsString('Log Viewer', json_encode($nav));

                    return true;
                }));
    }
}
