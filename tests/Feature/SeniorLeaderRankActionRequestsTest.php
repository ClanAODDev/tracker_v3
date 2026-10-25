<?php

namespace Tests\Feature;

use App\Enums\Position;
use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource\Pages\CreateRankAction;
use App\Models\Member;
use App\Models\RankAction;
use App\Models\User;
use Filament\Facades\Filament;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class SeniorLeaderRankActionRequestsTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private User $seniorLeader;

    private $division;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(Client::class, fn () => new Client(['handler' => HandlerStack::create(new MockHandler(array_fill(0, 20, new Response(200))))]));
        Filament::setCurrentPanel('mod');

        $this->division     = $this->createActiveDivision();
        $this->seniorLeader = $this->createSeniorLeader($this->division, [
            'rank'     => Rank::STAFF_SERGEANT,
            'position' => Position::MEMBER,
        ]);
        $this->actingAs($this->seniorLeader);
    }

    private function member(Rank $rank, ?int $unitId = null, ?int $divisionId = null): Member
    {
        return $this->createMember([
            'rank'        => $rank,
            'division_id' => $divisionId ?? $this->division->id,
            'unit_id'     => $unitId,
        ]);
    }

    private function request(Member $member): RankAction
    {
        Livewire::test(CreateRankAction::class)
            ->fillForm(['member_id' => $member->id, 'action' => 'promotion', 'Justification' => 'Great work'])
            ->call('create')
            ->assertHasNoFormErrors();

        return RankAction::where('member_id', $member->id)->firstOrFail();
    }

    #[Test]
    public function they_can_pick_members_from_every_unit_in_their_division_within_the_rank_limits(): void
    {
        $platoonA = $this->createPlatoon($this->division);
        $platoonB = $this->createPlatoon($this->division);

        $inA        = $this->member(Rank::PRIVATE, $platoonA->id);
        $inB        = $this->member(Rank::CORPORAL, $platoonB->id);
        $noUnit     = $this->member(Rank::CADET);
        $ceiling    = $this->member(Rank::STAFF_SERGEANT, $platoonA->id);
        $other      = $this->member(Rank::PRIVATE, null, $this->createActiveDivision()->id);
        $outranking = $this->member(Rank::MASTER_SERGEANT, $platoonA->id);

        $eligible = Member::eligibleForRankAction($this->seniorLeader)->pluck('id');

        $this->assertTrue($eligible->contains($inA->id));
        $this->assertTrue($eligible->contains($inB->id));
        $this->assertTrue($eligible->contains($noUnit->id));
        $this->assertFalse($eligible->contains($ceiling->id));
        $this->assertFalse($eligible->contains($other->id));
        $this->assertFalse($eligible->contains($outranking->id));
        $this->assertFalse($eligible->contains($this->seniorLeader->member_id));
    }

    #[Test]
    public function a_member_of_another_division_cannot_be_submitted(): void
    {
        $other = $this->member(Rank::PRIVATE, null, $this->createActiveDivision()->id);

        Livewire::test(CreateRankAction::class)
            ->fillForm(['member_id' => $other->id, 'action' => 'promotion', 'Justification' => 'x'])
            ->call('create')
            ->assertHasFormErrors(['member_id']);
    }

    #[Test]
    public function a_promotion_up_to_the_platoon_leader_limit_is_approved_immediately(): void
    {
        $action = $this->request($this->member(Rank::PRIVATE));

        $this->assertSame(Rank::PRIVATE_FIRST_CLASS, $action->rank);
        $this->assertNotNull($action->approved_at);
    }

    #[Test]
    public function promotions_above_the_limit_wait_for_a_division_leader(): void
    {
        $action = $this->request($this->member(Rank::PRIVATE_FIRST_CLASS));
        $this->assertSame(Rank::SPECIALIST, $action->rank);
        $this->assertNull($action->approved_at);

        $nco = $this->request($this->member(Rank::CORPORAL));
        $this->assertSame(Rank::SERGEANT, $nco->rank);
        $this->assertNull($nco->approved_at);
    }

    #[Test]
    public function they_cannot_approve_corporal_level_promotions_themselves(): void
    {
        $lanceCorporal = $this->request($this->member(Rank::TRAINER));

        $this->assertSame(Rank::LANCE_CORPORAL, $lanceCorporal->rank);
        $this->assertFalse($this->seniorLeader->can('approve', $lanceCorporal));
    }
}
