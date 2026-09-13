<?php

namespace Tests\Feature\Controllers;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class PmControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function it_renders_grouped_pm_links_and_omits_members_who_decline_pms()
    {
        $officer   = $this->createOfficer();
        $division  = $officer->member->division;
        $willing   = Member::factory()->create(['division_id' => $division->id, 'allow_pm' => true]);
        $declining = Member::factory()->create(['division_id' => $division->id, 'allow_pm' => false]);

        $this->actingAs($officer)
            ->post(route('private-message.create', $division->slug), [
                'pm-member-data' => "{$willing->clan_id},{$declining->clan_id}",
            ])
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('division/create-pm')
                ->where('recipientCount', 1)
                ->where('omitted', [$declining->name])
                ->has('groups', 1));
    }

    #[Test]
    public function it_is_forbidden_for_the_member_role()
    {
        $user   = $this->createMemberWithUser();
        $target = $this->createMember();

        $this->actingAs($user)
            ->post(route('private-message.create', $user->member->division->slug), [
                'pm-member-data' => "{$user->member->clan_id},{$target->clan_id}",
            ])
            ->assertForbidden();
    }
}
