<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RecruitingControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_index_displays_recruit_page()
    {
        $officer = $this->createOfficer();

        $response = $this->actingAs($officer)
            ->get(route('recruiting.initial'));

        $response->assertOk();
        $response->assertViewIs('recruit.index');
        $response->assertViewHas('divisions');
    }

    public function test_index_requires_authentication()
    {
        $response = $this->get(route('recruiting.initial'));

        $response->assertRedirect('/login');
    }

    public function test_member_cannot_access_recruitment()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser(['division_id' => $division->id]);
        $user->role_id = 1;
        $user->save();

        $response = $this->actingAs($user)
            ->get(route('recruiting.initial'));

        $response->assertForbidden();
    }

    public function test_form_displays_for_active_division()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();

        $response = $this->actingAs($officer)
            ->get(route('recruiting.form', $division->slug));

        $response->assertOk();
        $response->assertViewIs('recruit.form');
        $response->assertViewHas('division');
    }

    public function test_form_redirects_for_shutdown_division()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $division->shutdown_at = now()->subDay();
        $division->save();

        $response = $this->actingAs($officer)
            ->get(route('recruiting.form', $division->slug));

        $response->assertRedirect();
    }

    public function test_get_division_recruit_data_returns_json()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $this->createSquad($platoon);

        $response = $this->actingAs($officer)
            ->getJson(route('recruiting.divisionData', $division->slug));

        $response->assertOk();
        $response->assertJsonStructure([
            'platoons',
            'threads',
            'tasks',
            'welcome_area',
            'welcome_pm',
            'use_welcome_thread',
            'locality',
        ]);
    }

    public function test_submit_recruitment_creates_member()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $squad = $this->createSquad($platoon);

        $response = $this->actingAs($officer)
            ->post(route('recruiting.addMember'), [
                'division' => $division->slug,
                'member_id' => 99999,
                'forum_name' => 'TestRecruit',
                'rank' => Rank::RECRUIT->value,
                'platoon' => $platoon->id,
                'squad' => $squad->id,
            ]);

        $this->assertDatabaseHas('members', [
            'clan_id' => 99999,
            'name' => 'TestRecruit',
            'division_id' => $division->id,
        ]);
    }

    public function test_submit_recruitment_creates_transfer_record()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);

        $response = $this->actingAs($officer)
            ->post(route('recruiting.addMember'), [
                'division' => $division->slug,
                'member_id' => 88888,
                'forum_name' => 'TransferTestRecruit',
                'rank' => Rank::RECRUIT->value,
                'platoon' => $platoon->id,
            ]);

        $this->assertDatabaseHas('transfers', [
            'division_id' => $division->id,
        ]);
    }

    public function test_submit_recruitment_creates_rank_action()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);

        $response = $this->actingAs($officer)
            ->post(route('recruiting.addMember'), [
                'division' => $division->slug,
                'member_id' => 77777,
                'forum_name' => 'RankTestRecruit',
                'rank' => Rank::RECRUIT->value,
                'platoon' => $platoon->id,
            ]);

        $this->assertDatabaseHas('rank_actions', [
            'rank' => Rank::RECRUIT->value,
            'justification' => 'New recruit',
        ]);
    }
}
