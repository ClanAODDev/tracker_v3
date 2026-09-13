<?php

namespace Tests\Feature\Support;

use App\Enums\Rank;
use App\Support\DivisionToolbar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionToolbarTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function keys(array $tools): array
    {
        return array_column($tools, 'key');
    }

    #[Test]
    public function a_senior_leader_gets_the_management_tools()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;

        $this->actingAs($srLdr);
        $keys = $this->keys(DivisionToolbar::for($division, $srLdr, includeHome: true));

        $this->assertSame('home', $keys[0]);
        $this->assertContains('members', $keys);
        $this->assertContains('recruit', $keys);
        $this->assertContains('reports', $keys);
        $this->assertContains('structure', $keys);
        $this->assertContains('part-timers', $keys);
        $this->assertContains('leave', $keys);
        $this->assertContains('notes', $keys);
    }

    #[Test]
    public function a_plain_member_only_gets_the_read_only_tools()
    {
        $user     = $this->createMemberWithUser(['rank' => Rank::RECRUIT]);
        $division = $user->member->division;

        $this->actingAs($user);
        $keys = $this->keys(DivisionToolbar::for($division, $user));

        $this->assertContains('members', $keys);
        $this->assertContains('reports', $keys);
        $this->assertNotContains('recruit', $keys);
        $this->assertNotContains('requests', $keys);
        $this->assertNotContains('leave', $keys);
        $this->assertNotContains('notes', $keys);
        $this->assertNotContains('home', $keys);
    }

    #[Test]
    public function add_recruit_is_hidden_for_a_shutdown_division()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $division->update(['shutdown_at' => now()->subDay()]);

        $this->actingAs($srLdr);
        $keys = $this->keys(DivisionToolbar::for($division->fresh(), $srLdr));

        $this->assertNotContains('recruit', $keys);
    }
}
