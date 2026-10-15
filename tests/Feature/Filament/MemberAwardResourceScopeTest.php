<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Filament\Mod\Resources\MemberAwardResource\Pages\CreateMemberAward;
use App\Filament\Mod\Resources\MemberAwardResource\Pages\EditMemberAward;
use App\Models\Award;
use App\Models\MemberAward;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberAwardResourceScopeTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('mod');
    }

    #[Test]
    public function division_leader_cannot_grant_an_award_from_another_division(): void
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();
        $leader        = $this->createSeniorLeader($division);
        $award         = Award::factory()->create(['division_id' => $otherDivision->id]);
        $recipient     = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($leader);

        Livewire::test(CreateMemberAward::class)
            ->fillForm([
                'award_id'  => $award->id,
                'member_id' => $recipient->id,
                'reason'    => 'Great work',
                'approved'  => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['award_id']);

        $this->assertDatabaseMissing('award_member', ['award_id' => $award->id]);
    }

    #[Test]
    public function division_leader_can_grant_an_award_from_their_own_division_and_reach_the_edit_page(): void
    {
        $division  = $this->createActiveDivision();
        $leader    = $this->createSeniorLeader($division);
        $award     = Award::factory()->create(['division_id' => $division->id]);
        $recipient = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($leader);

        Livewire::test(CreateMemberAward::class)
            ->fillForm([
                'award_id'  => $award->id,
                'member_id' => $recipient->id,
                'reason'    => 'Great work',
                'approved'  => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $memberAward = MemberAward::where('award_id', $award->id)->firstOrFail();

        Livewire::test(EditMemberAward::class, ['record' => $memberAward->getRouteKey()])
            ->assertSuccessful();
    }

    #[Test]
    public function admin_cannot_grant_a_division_specific_award_here(): void
    {
        $division  = $this->createActiveDivision();
        $admin     = $this->createAdmin();
        $award     = Award::factory()->create(['division_id' => $division->id]);
        $recipient = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($admin);

        Livewire::test(CreateMemberAward::class)
            ->fillForm([
                'award_id'  => $award->id,
                'member_id' => $recipient->id,
                'reason'    => 'Great work',
                'approved'  => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['award_id']);
    }

    #[Test]
    public function senior_leader_division_leader_can_request_a_clan_wide_award_and_reach_the_edit_page(): void
    {
        $division  = $this->createActiveDivision();
        $leader    = $this->createSeniorLeader($division);
        $award     = Award::factory()->create(['division_id' => null]);
        $recipient = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($leader);

        Livewire::test(CreateMemberAward::class)
            ->fillForm([
                'division_filter' => 'clan-wide',
                'award_id'        => $award->id,
                'member_id'       => $recipient->id,
                'reason'          => 'Five years of service',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $memberAward = MemberAward::where('award_id', $award->id)->firstOrFail();

        $this->assertFalse((bool) $memberAward->approved);

        Livewire::test(EditMemberAward::class, ['record' => $memberAward->getRouteKey()])
            ->assertSuccessful();
    }

    #[Test]
    public function senior_leader_division_leader_still_cannot_use_another_divisions_award(): void
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();
        $leader        = $this->createSeniorLeader($division);
        $award         = Award::factory()->create(['division_id' => $otherDivision->id]);
        $recipient     = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($leader);

        Livewire::test(CreateMemberAward::class)
            ->fillForm(['award_id' => $award->id, 'member_id' => $recipient->id, 'reason' => 'x'])
            ->call('create')
            ->assertHasFormErrors(['award_id']);
    }

    #[Test]
    public function officer_division_leader_cannot_request_a_clan_wide_award(): void
    {
        $division = $this->createActiveDivision();
        $leader   = $this->createOfficer($division);
        $leader->member->update(['position' => Position::COMMANDING_OFFICER]);
        $award     = Award::factory()->create(['division_id' => null]);
        $recipient = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($leader);

        Livewire::test(CreateMemberAward::class)
            ->fillForm(['award_id' => $award->id, 'member_id' => $recipient->id, 'reason' => 'x'])
            ->call('create')
            ->assertHasFormErrors(['award_id']);
    }
}
