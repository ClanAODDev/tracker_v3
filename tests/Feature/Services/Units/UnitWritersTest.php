<?php

namespace Tests\Feature\Services\Units;

use App\Enums\Position;
use App\Enums\Rank;
use App\Filament\Mod\Resources\MemberResource\Pages\EditMember;
use App\Jobs\ResetOrphanedUnitAssignments;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\Unit;
use App\Services\RecruitmentService;
use App\Services\Units\UnitAssignment;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitWritersTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private $division;

    private Platoon $platoon;

    private Squad $squad;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Notification::fake();
        Queue::fake();

        $this->division = $this->createActiveDivision();
        $this->platoon  = $this->createPlatoon($this->division);
        $this->squad    = $this->createSquad($this->platoon);
    }

    #[Test]
    public function moving_division_and_separating_clear_the_unit(): void
    {
        $leader = $this->createSquadLeader($this->squad, ['division_id' => $this->division->id]);
        $other  = $this->createActiveDivision();

        $leader->moveToDivision($other->id);

        $this->assertNull($leader->fresh()->unit_id);
        $this->assertNull($this->unit($this->squad)->leader_id);
        $this->assertSynced();

        $member = $this->assigned();
        $member->reset();

        $this->assertNull($member->fresh()->unit_id);
        $this->assertSynced();
    }

    #[Test]
    public function the_weekly_reset_clears_the_unit_too(): void
    {
        $member = $this->assigned();
        Member::whereKey($member->id)->update(['division_id' => $this->createActiveDivision()->id]);

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertSame([null, 0, 0], [$member->fresh()->unit_id, $member->fresh()->platoon_id, $member->fresh()->squad_id]);
        $this->assertSynced();
    }

    #[Test]
    public function recruiting_into_a_squad_sets_the_unit(): void
    {
        $recruiter = $this->createMember(['division_id' => $this->division->id]);

        $member = app(RecruitmentService::class)->createMember(999123, 'Recruit', $this->division, Rank::RECRUIT->value, $this->unitFor($this->platoon)->id, $this->unitFor($this->squad)->id, [], $recruiter);

        $this->assertSame($this->unit($this->squad)->id, $member->fresh()->unit_id);
        $this->assertSynced();
    }

    #[Test]
    public function bulk_move_assign_squad_and_unassign_endpoints_keep_the_unit_in_step(): void
    {
        $this->actingAs($this->createSeniorLeader($this->division));
        $member = $this->createMember(['division_id' => $this->division->id]);

        $this->postJson(route('bulk-transfer.store', $this->division->slug), ['member_ids' => [$member->clan_id], 'platoon_id' => $this->unit($this->platoon)->id])->assertOk();
        $this->assertSame($this->unit($this->platoon)->id, $member->fresh()->unit_id);

        $this->postJson('/members/assign-squad', ['member_id' => $member->id, 'unit_id' => $this->unit($this->squad)->id])->assertOk();
        $this->assertSame($this->unit($this->squad)->id, $member->fresh()->unit_id);

        $this->post(route('member.unassign', $member->clan_id))->assertRedirect();
        $this->assertNull($member->fresh()->unit_id);

        $this->postJson(route('member.assign-platoon', $member->clan_id), ['platoon_id' => $this->unit($this->platoon)->id])->assertOk();
        $this->assertSame($this->unit($this->platoon)->id, $member->fresh()->unit_id);
        $this->assertSynced();
    }

    #[Test]
    public function assigning_a_platoon_keeps_a_squad_already_in_that_platoon(): void
    {
        $this->actingAs($this->createSeniorLeader($this->division));
        $member = $this->assigned();

        $this->postJson(route('member.assign-platoon', $member->clan_id), ['platoon_id' => $this->unit($this->platoon)->id])->assertOk();

        $this->assertSame($this->unit($this->squad)->id, $member->fresh()->unit_id);
    }

    #[Test]
    public function the_filament_member_form_sets_the_unit(): void
    {
        Filament::setCurrentPanel('mod');
        $this->actingAs($this->createAdmin());
        $member = $this->createMember(['division_id' => $this->division->id, 'position' => Position::MEMBER]);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['platoon_id' => $this->platoon->id, 'squad_id' => $this->squad->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($this->unit($this->squad)->id, $member->fresh()->unit_id);
        $this->assertSynced();
    }

    private function assigned(): Member
    {
        return $this->createMember(['division_id' => $this->division->id, 'platoon_id' => $this->platoon->id, 'squad_id' => $this->squad->id]);
    }

    private function unit(Platoon|Squad $legacy): Unit
    {
        return app(UnitAssignment::class)->forLegacy($legacy)->fresh();
    }

    private function assertSynced(): void
    {
        $this->artisan('tracker:units-verify')->assertSuccessful();
    }
}
