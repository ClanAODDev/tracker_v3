<?php

namespace Tests\Feature\Authorization;

use App\Enums\Ability;
use App\Enums\Role;
use App\Models\UserAbility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RoleViewAsTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private const array PRIVILEGED_LINKS = ['Add New Recruit', 'Log Viewer', 'Admin Panel', 'Operations'];

    private function navLabels(array $nav): array
    {
        $labels = [];
        array_walk_recursive($nav, function ($value, $key) use (&$labels) {
            if ($key === 'label') {
                $labels[] = $value;
            }
        });

        return $labels;
    }

    #[Test]
    public function an_admin_granted_view_logs_sees_privileged_links_normally(): void
    {
        $admin = $this->createAdmin();
        UserAbility::create(['user_id' => $admin->id, 'ability' => Ability::ViewLogs->value, 'reason' => 'test']);

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('nav', function ($nav) {
                $labels = $this->navLabels($nav->toArray());

                foreach (self::PRIVILEGED_LINKS as $link) {
                    $this->assertContains($link, $labels);
                }

                return true;
            }));
    }

    #[Test]
    public function viewing_as_a_member_hides_links_the_member_role_lacks(): void
    {
        $admin = $this->createAdmin();
        UserAbility::create(['user_id' => $admin->id, 'ability' => Ability::ViewLogs->value, 'reason' => 'test']);

        $this->actingAs($admin)
            ->withSession(['impersonatingRole' => Role::MEMBER->value])
            ->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.permissions.canViewAsRole', false)
                ->where('nav', function ($nav) {
                    $labels = $this->navLabels($nav->toArray());

                    foreach (self::PRIVILEGED_LINKS as $link) {
                        $this->assertNotContains($link, $labels);
                    }

                    return true;
                }));
    }

    #[Test]
    public function viewing_as_a_member_blocks_developer_only_pages(): void
    {
        $this->actingAs($this->createAdmin())
            ->withSession(['impersonatingRole' => Role::MEMBER->value])
            ->get('/log-viewer')
            ->assertForbidden();
    }

    #[Test]
    public function viewing_as_a_role_ignores_abilities_granted_to_the_user(): void
    {
        $officer = $this->createOfficer();
        UserAbility::create(['user_id' => $officer->id, 'ability' => Ability::ViewAdminPages->value, 'reason' => 'test']);

        $this->actingAs($officer);
        $this->assertTrue($officer->can(Ability::ViewAdminPages));

        session(['impersonatingRole' => Role::MEMBER->value]);
        $this->app->forgetScopedInstances();

        $this->assertFalse($officer->can(Ability::ViewAdminPages));
    }

    #[Test]
    public function the_role_view_can_always_be_ended(): void
    {
        $this->actingAs($this->createAdmin())
            ->withSession(['impersonatingRole' => Role::MEMBER->value])
            ->post(route('end-role-impersonation'))
            ->assertRedirect()
            ->assertSessionMissing('impersonatingRole');
    }
}
