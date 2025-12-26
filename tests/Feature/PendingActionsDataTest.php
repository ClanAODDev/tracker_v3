<?php

namespace Tests\Feature;

use App\Data\PendingActionsData;
use App\Enums\Position;
use App\Enums\Role;
use App\Models\Award;
use App\Models\Division;
use App\Models\Leave;
use App\Models\Member;
use App\Models\MemberAward;
use App\Models\Platoon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesPendingActionItems;

final class PendingActionsDataTest extends TestCase
{
    use CreatesPendingActionItems;
    use RefreshDatabase;

    protected Division $division;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerMockFilamentRoutes();

        $this->division = Division::factory()->create();
    }

    protected function registerMockFilamentRoutes(): void
    {
        $router = app('router');
        $router->get('/mod/member-awards', fn () => null)
            ->name('filament.mod.resources.member-awards.index');
        $router->get('/admin/tickets', fn () => null)
            ->name('filament.admin.resources.tickets.index');
        $router->getRoutes()->refreshNameLookups();
    }

    #[Test]
    public function admin_only_actions_are_marked_as_admin_only(): void
    {
        $user = $this->createUserWithRole('admin', Position::COMMANDING_OFFICER);

        $globalAward = Award::factory()->global()->create();
        MemberAward::factory()->pending()->create([
            'award_id' => $globalAward->id,
        ]);

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $clanAwardAction = $pendingActions->get('clan-award-requests');
        $this->assertNotNull($clanAwardAction);
        $this->assertTrue($clanAwardAction->adminOnly);
    }

    #[Test]
    public function division_actions_excludes_admin_only_items(): void
    {
        $user = $this->createUserWithRole('admin', Position::COMMANDING_OFFICER);

        $globalAward = Award::factory()->global()->create();
        MemberAward::factory()->pending()->create([
            'award_id' => $globalAward->id,
        ]);

        $divisionAward = Award::factory()->create(['division_id' => $this->division->id]);
        MemberAward::factory()->pending()->create([
            'award_id' => $divisionAward->id,
        ]);

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $allActions = $pendingActions->actions;
        $divisionActions = $pendingActions->divisionActions();

        $this->assertTrue($allActions->contains(fn ($a) => $a->key === 'clan-award-requests'));
        $this->assertFalse($divisionActions->contains(fn ($a) => $a->key === 'clan-award-requests'));
        $this->assertTrue($divisionActions->contains(fn ($a) => $a->key === 'award-requests'));
    }

    #[Test]
    public function non_admin_users_do_not_see_clan_award_requests(): void
    {
        $user = $this->createUserWithRole('sr_ldr', Position::COMMANDING_OFFICER);

        $globalAward = Award::factory()->global()->create();
        MemberAward::factory()->pending()->create([
            'award_id' => $globalAward->id,
        ]);

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $this->assertNull($pendingActions->get('clan-award-requests'));
    }

    #[Test]
    public function division_leaders_see_division_award_requests(): void
    {
        $user = $this->createUserWithRole('sr_ldr', Position::COMMANDING_OFFICER);

        $divisionAward = Award::factory()->create(['division_id' => $this->division->id]);
        MemberAward::factory()->pending()->create([
            'award_id' => $divisionAward->id,
        ]);

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $awardAction = $pendingActions->get('award-requests');
        $this->assertNotNull($awardAction);
        $this->assertEquals(1, $awardAction->count);
        $this->assertFalse($awardAction->adminOnly);
    }

    #[Test]
    public function non_division_leaders_do_not_see_division_award_requests(): void
    {
        $user = $this->createUserWithRole('officer', Position::SQUAD_LEADER);

        $divisionAward = Award::factory()->create(['division_id' => $this->division->id]);
        MemberAward::factory()->pending()->create([
            'award_id' => $divisionAward->id,
        ]);

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $this->assertNull($pendingActions->get('award-requests'));
    }

    #[Test]
    public function senior_leaders_see_pending_leaves(): void
    {
        $user = $this->createUserWithRole('sr_ldr');

        $member = Member::factory()->create(['division_id' => $this->division->id]);
        Leave::factory()->create([
            'member_id' => $member->clan_id,
            'approver_id' => null,
        ]);

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $leaveAction = $pendingActions->get('pending-leaves');
        $this->assertNotNull($leaveAction);
        $this->assertEquals(1, $leaveAction->count);
    }

    #[Test]
    public function junior_leaders_do_not_see_pending_leaves(): void
    {
        $user = $this->createUserWithRole('jr_ldr');

        $member = Member::factory()->create(['division_id' => $this->division->id]);
        Leave::factory()->create([
            'member_id' => $member->clan_id,
            'approver_id' => null,
        ]);

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $this->assertNull($pendingActions->get('pending-leaves'));
    }

    #[Test]
    public function leaders_see_voice_issues(): void
    {
        $user = $this->createUserWithRole('jr_ldr');

        Member::factory()->create([
            'division_id' => $this->division->id,
            'last_voice_status' => \App\Enums\DiscordStatus::NEVER_CONNECTED,
        ]);

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $voiceAction = $pendingActions->get('voice-issues');
        $this->assertNotNull($voiceAction);
        $this->assertEquals(1, $voiceAction->count);
    }

    #[Test]
    public function unassigned_members_action_has_correct_url(): void
    {
        $user = $this->createUserWithRole('sr_ldr', Position::COMMANDING_OFFICER);

        Member::factory()->create([
            'division_id' => $this->division->id,
            'platoon_id' => 0,
        ]);

        $this->division->refresh();

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $unassignedAction = $pendingActions->get('unassigned-members');
        $this->assertNotNull($unassignedAction);
        $this->assertStringContainsString('#platoons', $unassignedAction->url);
    }

    #[Test]
    public function no_squad_action_has_modal_target(): void
    {
        $user = $this->createUserWithRole('sr_ldr');

        $platoon = Platoon::factory()->create(['division_id' => $this->division->id]);
        Member::factory()->create([
            'division_id' => $this->division->id,
            'platoon_id' => $platoon->id,
            'squad_id' => 0,
            'position' => Position::MEMBER,
        ]);

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $noSquadAction = $pendingActions->get('unassigned-to-squad');
        $this->assertNotNull($noSquadAction);
        $this->assertEquals('no-squad-modal', $noSquadAction->modalTarget);
    }

    #[Test]
    public function has_any_actions_returns_false_when_empty(): void
    {
        $user = $this->createUserWithRole('member');

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $this->assertFalse($pendingActions->hasAnyActions());
        $this->assertEquals(0, $pendingActions->total());
    }

    #[Test]
    public function total_sums_all_action_counts(): void
    {
        $user = $this->createUserWithRole('admin', Position::COMMANDING_OFFICER);

        $divisionAward = Award::factory()->create(['division_id' => $this->division->id]);
        MemberAward::factory()->pending()->count(2)->create([
            'award_id' => $divisionAward->id,
        ]);

        $globalAward = Award::factory()->global()->create();
        MemberAward::factory()->pending()->count(3)->create([
            'award_id' => $globalAward->id,
        ]);

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $this->assertEquals(5, $pendingActions->total());
    }

    #[Test]
    public function admin_sees_admin_only_action_types(): void
    {
        $user = $this->createUserWithRole('admin', Position::COMMANDING_OFFICER);

        $this->createClanAwardRequests();
        $this->createOpenTickets();

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $this->assertTrue($pendingActions->hasAnyActions());
        $this->assertNotNull($pendingActions->get('clan-award-requests'));
        $this->assertNotNull($pendingActions->get('open-tickets'));
    }

    #[Test]
    public function senior_leader_sees_division_action_types(): void
    {
        $user = $this->createUserWithRole('sr_ldr', Position::COMMANDING_OFFICER);

        $this->createAllPendingActionItems($this->division);

        $pendingActions = PendingActionsData::forDivision($this->division, $user);

        $this->assertTrue($pendingActions->hasAnyActions());
        $this->assertNotNull($pendingActions->get('inactive-members'));
        $this->assertNotNull($pendingActions->get('award-requests'));
        $this->assertNotNull($pendingActions->get('pending-transfers'));
        $this->assertNotNull($pendingActions->get('pending-leaves'));
        $this->assertNotNull($pendingActions->get('voice-issues'));
        $this->assertNotNull($pendingActions->get('unassigned-members'));
        $this->assertNotNull($pendingActions->get('unassigned-to-squad'));
    }

    #[Test]
    public function division_actions_excludes_all_admin_only_items(): void
    {
        $user = $this->createUserWithRole('admin', Position::COMMANDING_OFFICER);

        $this->createClanAwardRequests();
        $this->createOpenTickets();

        $pendingActions = PendingActionsData::forDivision($this->division, $user);
        $divisionActions = $pendingActions->divisionActions();

        $this->assertFalse($divisionActions->contains(fn ($a) => $a->key === 'clan-award-requests'));
        $this->assertFalse($divisionActions->contains(fn ($a) => $a->key === 'open-tickets'));
    }

    #[Test]
    public function home_page_shows_admin_only_actions_for_admin(): void
    {
        $user = $this->createUserWithRole('admin', Position::COMMANDING_OFFICER);

        $this->createClanAwardRequests();
        $this->createOpenTickets();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Clan Award');
        $response->assertSee('Open Ticket');
    }

    #[Test]
    public function home_page_shows_division_actions_for_senior_leader(): void
    {
        $user = $this->createUserWithRole('sr_ldr', Position::COMMANDING_OFFICER);

        $this->createDivisionAwardRequests($this->division);
        $this->createVoiceIssues($this->division);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Award');
        $response->assertSee('Voice Issue');
    }

    #[Test]
    public function division_page_excludes_admin_only_actions_for_admin(): void
    {
        $user = $this->createUserWithRole('admin', Position::COMMANDING_OFFICER);

        $this->createClanAwardRequests();
        $this->createOpenTickets();

        $response = $this->actingAs($user)->get(route('division', $this->division));

        $response->assertStatus(200);
        $response->assertDontSee('Clan Award');
        $response->assertDontSee('Open Ticket');
    }

    #[Test]
    public function division_page_shows_division_actions_for_senior_leader(): void
    {
        $user = $this->createUserWithRole('sr_ldr', Position::COMMANDING_OFFICER);

        $this->createDivisionAwardRequests($this->division);
        $this->createVoiceIssues($this->division);

        $response = $this->actingAs($user)->get(route('division', $this->division));

        $response->assertStatus(200);
        $response->assertSee('Award');
        $response->assertSee('Voice Issue');
    }

    protected function createUserWithRole(string $roleName, ?Position $position = null): User
    {
        $role = Role::fromSlug($roleName);
        $user = User::factory()->create(['role_id' => $role->value]);
        $user->member->update([
            'division_id' => $this->division->id,
            'position' => $position ?? Position::MEMBER,
        ]);

        $this->actingAs($user);

        return $user;
    }
}
