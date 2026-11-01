<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource\Pages\CreateRankAction;
use App\Filament\Mod\Resources\RankActionResource\Pages\EditRankAction;
use App\Jobs\UpdateRankForMember;
use App\Models\Division;
use App\Models\Member;
use App\Models\RankAction;
use App\Models\User;
use App\Notifications\DM\NotifyMemberPromotionPendingAcceptance;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class JuniorPromotionAcceptanceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private Division $division;

    private User $approver;

    protected function setUp(): void
    {
        parent::setUp();

        config(['aod.rank.update_forums' => false]);
        Notification::fake();
        Filament::setCurrentPanel('mod');

        $this->division = $this->createActiveDivision();
        $this->approver = $this->createOfficer($this->division, [
            'rank'     => Rank::STAFF_SERGEANT,
            'position' => Position::EXECUTIVE_OFFICER,
        ]);
        $this->actingAs($this->approver);
    }

    private function pendingPromotion(Rank $from, Rank $to): RankAction
    {
        $member = $this->createMember(['division_id' => $this->division->id, 'rank' => $from]);

        return RankAction::factory()->create([
            'member_id'    => $member->id,
            'requester_id' => $this->createMember(['division_id' => $this->division->id])->id,
            'rank'         => $to,
        ]);
    }

    #[Test]
    public function approving_a_promotion_up_to_pfc_applies_it_without_asking_the_member(): void
    {
        $action = $this->pendingPromotion(Rank::PRIVATE, Rank::PRIVATE_FIRST_CLASS);

        Livewire::test(EditRankAction::class, ['record' => $action->getRouteKey()])
            ->callAction('approve')
            ->assertHasNoActionErrors();

        $action->refresh();
        $this->assertNotNull($action->accepted_at);
        $this->assertSame($this->approver->member_id, $action->approver_id);
        $this->assertSame(Rank::PRIVATE_FIRST_CLASS, Member::find($action->member_id)->rank);
        Notification::assertNotSentTo($action->member, NotifyMemberPromotionPendingAcceptance::class);
    }

    #[Test]
    public function approving_a_promotion_above_pfc_still_waits_for_the_member_to_accept(): void
    {
        $action = $this->pendingPromotion(Rank::PRIVATE_FIRST_CLASS, Rank::SPECIALIST);

        Livewire::test(EditRankAction::class, ['record' => $action->getRouteKey()])
            ->callAction('approve', data: ['notification_type' => 'now'])
            ->assertHasNoActionErrors();

        $action->refresh();
        $this->assertNotNull($action->approved_at);
        $this->assertNull($action->accepted_at);
        $this->assertSame(Rank::PRIVATE_FIRST_CLASS, Member::find($action->member_id)->rank);
        Notification::assertSentTo($action->member, NotifyMemberPromotionPendingAcceptance::class);
    }

    #[Test]
    public function creating_a_rank_action_you_can_approve_queues_one_rank_update(): void
    {
        Queue::fake();
        $member = $this->createMember(['division_id' => $this->division->id, 'rank' => Rank::PRIVATE]);

        Livewire::test(CreateRankAction::class)
            ->fillForm(['member_id' => $member->id, 'action' => 'promotion', 'promotion_rank' => Rank::PRIVATE_FIRST_CLASS->value, 'Justification' => 'Great work'])
            ->call('create')
            ->assertHasNoFormErrors();

        $action = RankAction::where('member_id', $member->id)->firstOrFail();
        $this->assertNotNull($action->accepted_at);
        Queue::assertPushed(UpdateRankForMember::class, 1);
        Notification::assertNotSentTo($member, NotifyMemberPromotionPendingAcceptance::class);
    }

    #[Test]
    public function there_is_no_separate_force_approval(): void
    {
        $action = $this->pendingPromotion(Rank::PRIVATE, Rank::PRIVATE_FIRST_CLASS);

        Livewire::test(EditRankAction::class, ['record' => $action->getRouteKey()])
            ->assertActionDoesNotExist('approve_with_force');
    }

    #[Test]
    public function only_promotions_above_pfc_require_acceptance(): void
    {
        $this->assertFalse($this->pendingPromotion(Rank::RECRUIT, Rank::CADET)->requiresAcceptance());
        $this->assertFalse($this->pendingPromotion(Rank::PRIVATE, Rank::PRIVATE_FIRST_CLASS)->requiresAcceptance());
        $this->assertTrue($this->pendingPromotion(Rank::PRIVATE_FIRST_CLASS, Rank::SPECIALIST)->requiresAcceptance());
        $this->assertFalse($this->pendingPromotion(Rank::SPECIALIST, Rank::PRIVATE)->requiresAcceptance());
    }
}
