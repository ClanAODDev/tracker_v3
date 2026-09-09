<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NavigationTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    /**
     * @return list<string>
     */
    private function navLabels(AssertableInertia $page): array
    {
        $flatten = function (array $items) use (&$flatten): array {
            $labels = [];
            foreach ($items as $item) {
                $labels[] = $item['label'] ?? null;
                if (! empty($item['children'])) {
                    $labels = array_merge($labels, $flatten($item['children']));
                }
            }

            return array_values(array_filter($labels));
        };

        return $flatten($page->toArray()['props']['nav'] ?? []);
    }

    #[Test]
    public function developer_with_member_role_sees_admin_nav_links()
    {
        $division             = $this->createActiveDivision();
        $developer            = $this->createMemberWithUser(['division_id' => $division->id]);
        $developer->developer = true;
        $developer->save();

        $this->actingAs($developer)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $labels = $this->navLabels($page);
                $this->assertContains('Admin Panel', $labels);
                $this->assertContains('Operations', $labels);
                $this->assertContains('Log Viewer', $labels);
            });
    }

    #[Test]
    public function regular_member_does_not_see_admin_nav_links()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $labels = $this->navLabels($page);
                $this->assertNotContains('Admin Panel', $labels);
                $this->assertNotContains('Operations', $labels);
                $this->assertNotContains('Log Viewer', $labels);
            });
    }

    #[Test]
    public function developer_with_member_role_gets_bulk_mode_enabled()
    {
        $division             = $this->createActiveDivision();
        $developer            = $this->createMemberWithUser(['division_id' => $division->id]);
        $developer->developer = true;
        $developer->save();

        $this->actingAs($developer)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('auth.permissions.canUseBulkMode', true));
    }

    #[Test]
    public function regular_member_does_not_get_bulk_mode_enabled()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('auth.permissions.canUseBulkMode', false));
    }
}
