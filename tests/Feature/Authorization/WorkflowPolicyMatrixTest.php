<?php

namespace Tests\Feature\Authorization;

use App\Enums\Rank;
use App\Enums\Role;
use App\Models\Division;
use App\Models\Leave;
use App\Models\MemberRequest;
use App\Models\RankAction;
use App\Models\Transfer;
use PHPUnit\Framework\Attributes\Test;

class WorkflowPolicyMatrixTest extends PermissionMatrixTestCase
{
    private const RANK_ACTION_MEMBERS = ['same_squad', 'same_platoon_other_squad', 'other_platoon', 'other_division'];

    private const RANK_ACTION_RANKS = ['private_first_class' => Rank::PRIVATE_FIRST_CLASS, 'specialist' => Rank::SPECIALIST, 'sergeant' => Rank::SERGEANT];

    #[Test]
    public function transfer_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $this->assertMatchesSnapshot('TransferPolicy', $this->evaluate(PermissionWorld::ACTORS, $this->transferChecks()));
    }

    #[Test]
    public function rank_action_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $this->assertMatchesSnapshot('RankActionPolicy', $this->evaluate(PermissionWorld::ACTORS, $this->rankActionChecks()));
    }

    #[Test]
    public function rank_action_policy_for_unit_leaders_without_the_officer_role_matches_the_recorded_matrix(): void
    {
        $this->buildWorld(Role::MEMBER);

        $this->assertMatchesSnapshot('RankActionPolicy.unit-leaders-with-member-role', $this->evaluate(['platoon_leader', 'squad_leader'], $this->rankActionChecks()));
    }

    #[Test]
    public function leave_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $checks = array_map(fn ($ability) => [$ability, '(any leave)', fn () => [Leave::class]], ['viewAny', 'create', 'update', 'deleteAny']);

        $this->assertMatchesSnapshot('LeavePolicy', $this->evaluate(PermissionWorld::ACTORS, $checks));
    }

    #[Test]
    public function member_request_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $byOther = MemberRequest::create([
            'member_id'    => $this->world->targets['same_squad']->id,
            'requester_id' => $this->world->targets['higher_rank']->id,
            'division_id'  => $this->world->divisionA->id,
        ]);

        $checks = [
            ['manage', '(any request)', fn () => [MemberRequest::class]],
            ['create', '(any request)', fn () => [MemberRequest::class]],
        ];

        foreach (['view', 'update', 'cancel', 'delete'] as $ability) {
            $checks[] = [$ability, 'requested_by_other', fn () => [$byOther->fresh()]];
            $checks[] = [$ability, 'requested_by_actor', fn (string $actor) => $this->requestBy($actor)];
        }

        $this->assertMatchesSnapshot('MemberRequestPolicy', $this->evaluate(PermissionWorld::ACTORS, $checks));
    }

    private function transferChecks(): array
    {
        $divisionC = Division::factory()->withoutHandles()->create(['name' => 'Division C', 'active' => true]);
        $incoming  = $this->world->targets['other_division'];
        $outgoing  = $this->world->targets['same_squad'];

        $transfers = [
            'incoming_pending'  => ['member_id' => $incoming->id, 'division_id' => $this->world->divisionA->id],
            'outgoing_pending'  => ['member_id' => $outgoing->id, 'division_id' => $this->world->divisionB->id],
            'incoming_approved' => ['member_id' => $incoming->id, 'division_id' => $this->world->divisionA->id, 'approved_at' => now()],
            'outgoing_approved' => ['member_id' => $outgoing->id, 'division_id' => $this->world->divisionB->id, 'approved_at' => now()],
            'unrelated_pending' => ['member_id' => $incoming->id, 'division_id' => $divisionC->id],
        ];

        $checks = [
            ['viewAny', '(any transfer)', fn () => [Transfer::class]],
            ['create', '(any transfer)', fn () => [Transfer::class]],
        ];

        foreach ($transfers as $label => $attributes) {
            $transfer = Transfer::create($attributes);

            foreach (['approve', 'hold', 'delete', 'manageComments'] as $ability) {
                $checks[] = [$ability, $label, fn () => [$transfer->fresh()]];
            }
        }

        return $checks;
    }

    private function rankActionChecks(): array
    {
        $requester = $this->world->targets['higher_rank'];

        $checks = [
            ['viewAny', '(any rank action)', fn () => [RankAction::class]],
            ['deleteAny', '(any rank action)', fn () => [RankAction::class]],
        ];

        $actions = [];

        foreach (self::RANK_ACTION_MEMBERS as $target) {
            foreach (self::RANK_ACTION_RANKS as $rankLabel => $rank) {
                $actions["{$target} to {$rankLabel}"] = RankAction::create([
                    'member_id'    => $this->world->targets[$target]->id,
                    'requester_id' => $requester->id,
                    'rank'         => $rank,
                ]);
            }
        }

        foreach (['update', 'approve', 'manageComments'] as $ability) {
            foreach ($actions as $label => $action) {
                $checks[] = [$ability, $label, fn () => [$action->fresh()]];
            }

            $checks[] = [$ability, 'self to specialist', fn (string $actor) => $this->rankActionFor($actor, $actor, $requester->id)];
            $checks[] = [$ability, 'same_squad to specialist, requested by actor', fn (string $actor) => $this->rankActionFor($actor, 'same_squad')];
        }

        return $checks;
    }

    private function rankActionFor(string $actor, string $target, ?int $requesterId = null): ?array
    {
        $actorMember  = $this->world->users[$actor]->member;
        $targetMember = $target === $actor ? $actorMember : $this->world->targets[$target];

        if (! $actorMember || ! $targetMember) {
            return null;
        }

        return [RankAction::create([
            'member_id'    => $targetMember->id,
            'requester_id' => $requesterId ?? $actorMember->id,
            'rank'         => Rank::SPECIALIST,
        ])];
    }

    private function requestBy(string $actor): ?array
    {
        $member = $this->world->users[$actor]->member;

        if (! $member) {
            return null;
        }

        return [MemberRequest::create([
            'member_id'    => $this->world->targets['same_squad']->id,
            'requester_id' => $member->id,
            'division_id'  => $this->world->divisionA->id,
        ])];
    }
}
