<?php

namespace Tests\Unit\Models;

use App\Enums\Rank;
use App\Models\RankAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RankActionTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function approve_sets_approved_at_and_approver_id()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $member    = $this->createMember();
        $requester = $this->createMember();

        $rankAction = RankAction::factory()->create([
            'member_id'    => $member->id,
            'requester_id' => $requester->id,
        ]);

        $rankAction->approve();

        $this->assertNotNull($rankAction->approved_at);
        $this->assertEquals($admin->member_id, $rankAction->approver_id);
    }

    #[Test]
    public function accept_sets_accepted_at()
    {
        $member     = $this->createMember();
        $rankAction = RankAction::factory()->create([
            'member_id' => $member->id,
        ]);

        $rankAction->accept();

        $this->assertNotNull($rankAction->accepted_at);
    }

    #[Test]
    public function decline_sets_declined_at()
    {
        $member     = $this->createMember();
        $rankAction = RankAction::factory()->create([
            'member_id' => $member->id,
        ]);

        $rankAction->decline();

        $this->assertNotNull($rankAction->declined_at);
    }

    #[Test]
    public function deny_sets_denied_at_and_reason()
    {
        $member     = $this->createMember();
        $rankAction = RankAction::factory()->create([
            'member_id' => $member->id,
        ]);

        $rankAction->deny('Not eligible for promotion');

        $this->assertNotNull($rankAction->denied_at);
        $this->assertEquals('Not eligible for promotion', $rankAction->deny_reason);
    }

    #[Test]
    public function award_sets_awarded_at()
    {
        $member     = $this->createMember();
        $rankAction = RankAction::factory()->create([
            'member_id' => $member->id,
        ]);

        $rankAction->award();

        $this->assertNotNull($rankAction->awarded_at);
    }

    #[Test]
    public function approve_and_accept_sets_all_timestamps()
    {
        $member     = $this->createMember();
        $rankAction = RankAction::factory()->create([
            'member_id' => $member->id,
        ]);

        $rankAction->approveAndAccept();

        $this->assertNotNull($rankAction->approved_at);
        $this->assertNotNull($rankAction->accepted_at);
        $this->assertNotNull($rankAction->awarded_at);
    }

    public static function approvalStatusProvider(): array
    {
        return [
            'approved'     => ['approved', true],
            'not approved' => ['pending',  false],
        ];
    }

    #[Test]
    #[DataProvider('approvalStatusProvider')]
    public function is_approved_reflects_approval_state(string $state, bool $expected): void
    {
        $member     = $this->createMember();
        $rankAction = RankAction::factory()->{$state}()->create(['member_id' => $member->id]);

        $this->assertSame($expected, (bool) $rankAction->isApproved());
    }

    public static function actionableProvider(): array
    {
        return [
            'pending'  => ['pending',  true],
            'approved' => ['approved', false],
            'denied'   => ['denied',   false],
        ];
    }

    #[Test]
    #[DataProvider('actionableProvider')]
    public function actionable_reflects_action_state(string $state, bool $expected): void
    {
        $member     = $this->createMember();
        $rankAction = RankAction::factory()->{$state}()->create(['member_id' => $member->id]);

        $this->assertSame($expected, $rankAction->actionable());
    }

    public static function resolvedByRecipientProvider(): array
    {
        return [
            'accepted'           => ['accepted', [],                                            true],
            'declined'           => ['declined', [],                                            true],
            'approved not acted' => ['approved', ['accepted_at' => null, 'declined_at' => null], false],
        ];
    }

    #[Test]
    #[DataProvider('resolvedByRecipientProvider')]
    public function resolved_by_recipient_reflects_recipient_resolution(string $state, array $overrides, bool $expected): void
    {
        $member     = $this->createMember();
        $rankAction = RankAction::factory()->{$state}()->create(
            array_merge(['member_id' => $member->id], $overrides)
        );

        $this->assertSame($expected, $rankAction->resolvedByRecipient());
    }

    #[Test]
    public function scope_pending_returns_unapproved_actions()
    {
        $member = $this->createMember();

        $pending  = RankAction::factory()->pending()->create(['member_id' => $member->id]);
        $approved = RankAction::factory()->approved()->accepted()->create(['member_id' => $member->id]);

        $results = RankAction::pending()->get();

        $this->assertTrue($results->contains($pending));
        $this->assertFalse($results->contains($approved));
    }

    #[Test]
    public function scope_pending_returns_approved_but_not_accepted()
    {
        $member = $this->createMember();

        $approvedNotAccepted = RankAction::factory()->approved()->create([
            'member_id'   => $member->id,
            'accepted_at' => null,
            'declined_at' => null,
        ]);

        $results = RankAction::pending()->get();

        $this->assertTrue($results->contains($approvedNotAccepted));
    }

    #[Test]
    public function scope_approved_and_accepted_returns_complete_actions()
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

    #[Test]
    public function scope_for_user_filters_by_division_for_non_admin()
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();

        $srLeader = $this->createSeniorLeader($division);

        $divisionMember = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::PRIVATE_FIRST_CLASS,
        ]);
        $otherMember = $this->createMember([
            'division_id' => $otherDivision->id,
            'rank'        => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $divisionAction = RankAction::factory()->create([
            'member_id' => $divisionMember->id,
            'rank'      => Rank::SPECIALIST,
        ]);
        $otherAction = RankAction::factory()->create([
            'member_id' => $otherMember->id,
            'rank'      => Rank::SPECIALIST,
        ]);

        $results = RankAction::forUser($srLeader)->get();

        $this->assertTrue($results->contains($divisionAction));
        $this->assertFalse($results->contains($otherAction));
    }
}
