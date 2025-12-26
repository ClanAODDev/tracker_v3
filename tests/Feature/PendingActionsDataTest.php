<?php

namespace Tests\Feature;

use App\Data\PendingActionsData;
use App\Enums\Position;
use App\Models\Award;
use App\Models\Division;
use App\Models\Leave;
use App\Models\Member;
use App\Models\MemberAward;
use App\Models\Platoon;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PendingActionsDataTest extends TestCase
{
    use RefreshDatabase;

    protected Division $division;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerMockFilamentRoutes();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->division = Division::factory()->create();
    }

    protected function registerMockFilamentRoutes(): void
    {
        $router = app('router');
        $router->get('/mod/member-awards', fn () => null)
            ->name('filament.mod.resources.member-awards.index');
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

        $clanAwardAction = $pendingActions->get('clan_award_requests');
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

        $this->assertTrue($allActions->contains(fn ($a) => $a->key === 'clan_award_requests'));
        $this->assertFalse($divisionActions->contains(fn ($a) => $a->key === 'clan_award_requests'));
        $this->assertTrue($divisionActions->contains(fn ($a) => $a->key === 'award_requests'));
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

        $this->assertNull($pendingActions->get('clan_award_requests'));
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

        $awardAction = $pendingActions->get('award_requests');
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

        $this->assertNull($pendingActions->get('award_requests'));
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

        $leaveAction = $pendingActions->get('pending_leaves');
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

        $this->assertNull($pendingActions->get('pending_leaves'));
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

        $voiceAction = $pendingActions->get('voice_issues');
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

        $unassignedAction = $pendingActions->get('unassigned_members');
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

        $noSquadAction = $pendingActions->get('unassigned_to_squad');
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

    protected function createUserWithRole(string $roleName, ?Position $position = null): User
    {
        $role = Role::where('name', $roleName)->first();
        $user = User::factory()->create(['role_id' => $role->id]);
        $user->member->update([
            'division_id' => $this->division->id,
            'position' => $position ?? Position::MEMBER,
        ]);

        $this->actingAs($user);

        return $user;
    }
}
