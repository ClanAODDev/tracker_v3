<?php

namespace Tests\Feature;

use App\AOD\MemberSync\GetDivisionInfo;
use App\Enums\Rank;
use App\Models\Member;
use App\Models\RankAction;
use App\Services\MemberSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Kirschbaum\Commentions\Comment;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RemovedMemberRankActionsTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function action(Member $member, array $attributes = []): RankAction
    {
        return RankAction::factory()->create([
            'member_id'    => $member->id,
            'requester_id' => $member->id,
            'rank'         => Rank::SPECIALIST,
            ...$attributes,
        ]);
    }

    #[Test]
    public function removing_a_member_discards_their_pending_rank_actions_and_keeps_their_history(): void
    {
        $officer = $this->createOfficer();
        $this->actingAs($officer);
        $member = $this->createMember(['division_id' => $officer->member->division_id]);

        $awaitingApproval   = $this->action($member);
        $awaitingAcceptance = $this->action($member, ['approved_at' => now()]);
        $accepted           = $this->action($member, ['approved_at' => now(), 'accepted_at' => now()]);
        $denied             = $this->action($member, ['denied_at' => now(), 'deny_reason' => 'Not yet']);
        $declined           = $this->action($member, ['approved_at' => now(), 'declined_at' => now()]);
        $otherMembers       = $this->action($this->createMember(['division_id' => $officer->member->division_id]));
        $awaitingApproval->comment('Ready for Spec', $officer);
        $this->assertSame(1, $awaitingApproval->comments()->count());

        $member->reset();

        $this->assertModelMissing($awaitingApproval);
        $this->assertModelMissing($awaitingAcceptance);
        $this->assertModelExists($accepted);
        $this->assertModelExists($denied);
        $this->assertModelExists($declined);
        $this->assertModelExists($otherMembers);
        $this->assertSame(0, Comment::where('commentable_type', $awaitingApproval->getMorphClass())->where('commentable_id', $awaitingApproval->id)->count());
    }

    #[Test]
    public function a_member_removed_by_the_forum_sync_loses_their_pending_rank_actions(): void
    {
        Notification::fake();
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id, 'clan_id' => 77777]);
        $pending  = $this->action($member, ['approved_at' => now()->subDays(3)]);

        $info       = Mockery::mock(GetDivisionInfo::class);
        $info->data = [[
            'userid'       => 99994, 'username' => 'AOD_Other', 'joindate' => '2024-01-01', 'aoddivision' => $division->name,
            'aodrankval'   => 3, 'discordtag' => 'o#1', 'discordid' => '999940000', 'postcount' => 5, 'allow_pm' => 1,
            'allow_export' => 'yes', 'tsid' => 'x', 'lastdiscord_status' => 'connected', 'lastactivity' => time(), 'lastdiscord_connect' => time(),
        ]];

        (new MemberSyncService($info))->sync();

        $this->assertSame(0, $member->fresh()->division_id);
        $this->assertModelMissing($pending);
    }
}
