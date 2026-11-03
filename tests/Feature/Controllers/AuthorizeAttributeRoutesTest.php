<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class AuthorizeAttributeRoutesTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function removing_a_part_timer_requires_managing_part_timers(): void
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $this->createActiveDivision()->id]);
        $division->partTimeMembers()->attach($member->id);

        $this->actingAs($this->createMemberWithUser())
            ->get(route('removePartTimer', [$division->slug, $member->clan_id]))
            ->assertForbidden();
        $this->assertTrue($division->partTimeMembers()->whereKey($member->id)->exists());

        $this->actingAs($this->createAdmin())
            ->get(route('removePartTimer', [$division->slug, $member->clan_id]))
            ->assertRedirect();
        $this->assertFalse($division->partTimeMembers()->whereKey($member->id)->exists());
    }

    #[Test]
    public function dropping_a_member_for_inactivity_requires_separating_them(): void
    {
        $member = $this->createMember(['division_id' => $this->createActiveDivision()->id]);

        $this->actingAs($this->createMemberWithUser())
            ->delete(route('member.drop-for-inactivity', $member->clan_id))
            ->assertForbidden();

        $this->actingAs($this->createAdmin())
            ->from('/')
            ->delete(route('member.drop-for-inactivity', $member->clan_id))
            ->assertSessionHasErrors('removal_reason');
    }
}
