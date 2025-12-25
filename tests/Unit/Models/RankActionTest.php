<?php

namespace Tests\Unit\Models;

use App\Enums\Rank;
use App\Models\RankAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RankActionTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_approve_sets_approved_at_and_approver_id()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $member = $this->createMember();
        $requester = $this->createMember();

        $rankAction = RankAction::factory()->create([
            'member_id' => $member->id,
            'requester_id' => $requester->id,
        ]);

        $rankAction->approve();

        $this->assertNotNull($rankAction->approved_at);
        $this->assertEquals($admin->member_id, $rankAction->approver_id);
    }

    public function test_accept_sets_accepted_at()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->create([
            'member_id' => $member->id,
        ]);

        $rankAction->accept();

        $this->assertNotNull($rankAction->accepted_at);
    }

    public function test_decline_sets_declined_at()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->create([
            'member_id' => $member->id,
        ]);

        $rankAction->decline();

        $this->assertNotNull($rankAction->declined_at);
    }

    public function test_deny_sets_denied_at_and_reason()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->create([
            'member_id' => $member->id,
        ]);

        $rankAction->deny('Not eligible for promotion');

        $this->assertNotNull($rankAction->denied_at);
        $this->assertEquals('Not eligible for promotion', $rankAction->deny_reason);
    }

    public function test_award_sets_awarded_at()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->create([
            'member_id' => $member->id,
        ]);

        $rankAction->award();

        $this->assertNotNull($rankAction->awarded_at);
    }

    public function test_approve_and_accept_sets_all_timestamps()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->create([
            'member_id' => $member->id,
        ]);

        $rankAction->approveAndAccept();

        $this->assertNotNull($rankAction->approved_at);
        $this->assertNotNull($rankAction->accepted_at);
        $this->assertNotNull($rankAction->awarded_at);
    }

    public function test_is_approved_returns_truthy_when_approved()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->approved()->create([
            'member_id' => $member->id,
        ]);

        $this->assertTrue((bool) $rankAction->isApproved());
    }

    public function test_is_approved_returns_falsy_when_not_approved()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->pending()->create([
            'member_id' => $member->id,
        ]);

        $this->assertFalse((bool) $rankAction->isApproved());
    }

    public function test_actionable_returns_true_when_pending()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->pending()->create([
            'member_id' => $member->id,
        ]);

        $this->assertTrue($rankAction->actionable());
    }

    public function test_actionable_returns_false_when_approved()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->approved()->create([
            'member_id' => $member->id,
        ]);

        $this->assertFalse($rankAction->actionable());
    }

    public function test_actionable_returns_false_when_denied()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->denied()->create([
            'member_id' => $member->id,
        ]);

        $this->assertFalse($rankAction->actionable());
    }

    public function test_resolved_by_recipient_returns_true_when_accepted()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->accepted()->create([
            'member_id' => $member->id,
        ]);

        $this->assertTrue($rankAction->resolvedByRecipient());
    }

    public function test_resolved_by_recipient_returns_true_when_declined()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->declined()->create([
            'member_id' => $member->id,
        ]);

        $this->assertTrue($rankAction->resolvedByRecipient());
    }

    public function test_resolved_by_recipient_returns_false_when_pending()
    {
        $member = $this->createMember();
        $rankAction = RankAction::factory()->approved()->create([
            'member_id' => $member->id,
            'accepted_at' => null,
            'declined_at' => null,
        ]);

        $this->assertFalse($rankAction->resolvedByRecipient());
    }

    public function test_scope_pending_returns_unapproved_actions()
    {
        $member = $this->createMember();

        $pending = RankAction::factory()->pending()->create(['member_id' => $member->id]);
        $approved = RankAction::factory()->approved()->accepted()->create(['member_id' => $member->id]);

        $results = RankAction::pending()->get();

        $this->assertTrue($results->contains($pending));
        $this->assertFalse($results->contains($approved));
    }

    public function test_scope_pending_returns_approved_but_not_accepted()
    {
        $member = $this->createMember();

        $approvedNotAccepted = RankAction::factory()->approved()->create([
            'member_id' => $member->id,
            'accepted_at' => null,
            'declined_at' => null,
        ]);

        $results = RankAction::pending()->get();

        $this->assertTrue($results->contains($approvedNotAccepted));
    }

    public function test_scope_approved_and_accepted_returns_complete_actions()
    {
        $member = $this->createMember();

        $complete = RankAction::factory()->approved()->accepted()->create([
            'member_id' => $member->id,
        ]);

        $pending = RankAction::factory()->pending()->create([
            'member_id' => $member->id,
        ]);

        $results = RankAction::approvedAndAccepted()->get();

        $this->assertTrue($results->contains($complete));
        $this->assertFalse($results->contains($pending));
    }

    public function test_scope_for_user_filters_by_division_for_non_admin()
    {
        $division = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();

        $srLeader = $this->createSeniorLeader($division);

        $divisionMember = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);
        $otherMember = $this->createMember([
            'division_id' => $otherDivision->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $divisionAction = RankAction::factory()->create([
            'member_id' => $divisionMember->id,
            'rank' => Rank::SPECIALIST,
        ]);
        $otherAction = RankAction::factory()->create([
            'member_id' => $otherMember->id,
            'rank' => Rank::SPECIALIST,
        ]);

        $results = RankAction::forUser($srLeader)->get();

        $this->assertTrue($results->contains($divisionAction));
        $this->assertFalse($results->contains($otherAction));
    }
}
