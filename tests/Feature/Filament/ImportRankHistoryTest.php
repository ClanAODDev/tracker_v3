<?php

namespace Tests\Feature\Filament;

use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource\Pages\ImportRankHistory;
use App\Models\RankAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ImportRankHistoryTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function msgt_can_access_import_page(): void
    {
        $user = $this->createMasterSergeant();

        $this->actingAs($user)
            ->get(route('filament.mod.resources.rank-actions.import-history'))
            ->assertOk();
    }

    #[Test]
    public function below_msgt_cannot_access_import_page(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id, 'rank' => Rank::STAFF_SERGEANT]);

        $this->actingAs($user)
            ->get(route('filament.mod.resources.rank-actions.import-history'))
            ->assertForbidden();
    }

    #[Test]
    public function guest_cannot_access_import_page(): void
    {
        $this->get(route('filament.mod.resources.rank-actions.import-history'))
            ->assertRedirect();
    }

    #[Test]
    public function import_creates_rank_action_records(): void
    {
        $user   = $this->createMasterSergeant();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        $this->actingAs($user);

        Livewire::test(ImportRankHistory::class)
            ->set('data.member_id', $target->id)
            ->set('data.entries', [
                ['rank' => (string) Rank::PRIVATE->value, 'date' => '2020-03-01'],
                ['rank' => (string) Rank::SPECIALIST->value, 'date' => '2020-09-01'],
            ])
            ->call('create');

        $this->assertDatabaseCount('rank_actions', 2);
    }

    #[Test]
    public function import_sets_historical_dates_on_approved_and_accepted(): void
    {
        $user   = $this->createMasterSergeant();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        $this->actingAs($user);

        Livewire::test(ImportRankHistory::class)
            ->set('data.member_id', $target->id)
            ->set('data.entries', [
                ['rank' => (string) Rank::PRIVATE->value, 'date' => '2021-06-15'],
            ])
            ->call('create');

        $action = RankAction::first();
        $this->assertEquals('2021-06-15', $action->approved_at->toDateString());
        $this->assertEquals('2021-06-15', $action->accepted_at->toDateString());
    }

    #[Test]
    public function import_sets_awarded_at_for_officer_ranks(): void
    {
        $user   = $this->createMasterSergeant();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        $this->actingAs($user);

        Livewire::test(ImportRankHistory::class)
            ->set('data.member_id', $target->id)
            ->set('data.entries', [
                ['rank' => (string) Rank::SERGEANT->value, 'date' => '2022-01-01'],
            ])
            ->call('create');

        $action = RankAction::first();
        $this->assertNotNull($action->awarded_at);
        $this->assertEquals('2022-01-01', $action->awarded_at->toDateString());
    }

    #[Test]
    public function import_does_not_set_awarded_at_for_enlisted_ranks(): void
    {
        $user   = $this->createMasterSergeant();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        $this->actingAs($user);

        Livewire::test(ImportRankHistory::class)
            ->set('data.member_id', $target->id)
            ->set('data.entries', [
                ['rank' => (string) Rank::SPECIALIST->value, 'date' => '2020-01-01'],
            ])
            ->call('create');

        $this->assertNull(RankAction::first()->awarded_at);
    }

    #[Test]
    public function import_sets_requester_and_approver_to_current_user(): void
    {
        $user   = $this->createMasterSergeant();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        $this->actingAs($user);

        Livewire::test(ImportRankHistory::class)
            ->set('data.member_id', $target->id)
            ->set('data.entries', [
                ['rank' => (string) Rank::PRIVATE->value, 'date' => '2020-01-01'],
            ])
            ->call('create');

        $action = RankAction::first();
        $this->assertEquals($user->member_id, $action->requester_id);
        $this->assertEquals($user->member_id, $action->approver_id);
    }

    #[Test]
    public function import_marks_entries_as_historical(): void
    {
        $user   = $this->createMasterSergeant();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        $this->actingAs($user);

        Livewire::test(ImportRankHistory::class)
            ->set('data.member_id', $target->id)
            ->set('data.entries', [
                ['rank' => (string) Rank::PRIVATE->value, 'date' => '2020-01-01'],
            ])
            ->call('create');

        $this->assertEquals('Historical entry', RankAction::first()->justification);
    }

    #[Test]
    public function selecting_member_pre_populates_entries_with_existing_rank_history(): void
    {
        $user   = $this->createMasterSergeant();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        RankAction::create([
            'member_id'     => $target->id,
            'requester_id'  => $user->member_id,
            'approver_id'   => $user->member_id,
            'rank'          => Rank::PRIVATE->value,
            'approved_at'   => '2020-01-01',
            'accepted_at'   => '2020-01-01',
            'justification' => 'Historical entry',
        ]);

        RankAction::create([
            'member_id'     => $target->id,
            'requester_id'  => $user->member_id,
            'approver_id'   => $user->member_id,
            'rank'          => Rank::SPECIALIST->value,
            'approved_at'   => '2021-06-01',
            'accepted_at'   => '2021-06-01',
            'justification' => 'Historical entry',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(ImportRankHistory::class)
            ->set('data.member_id', $target->id);

        $entries = $component->get('data.entries');

        $this->assertCount(2, $entries);
        $this->assertEquals((string) Rank::PRIVATE->value, $entries[0]['rank']);
        $this->assertEquals('2020-01-01', $entries[0]['date']);
        $this->assertEquals((string) Rank::SPECIALIST->value, $entries[1]['rank']);
        $this->assertEquals('2021-06-01', $entries[1]['date']);
    }

    protected function createMasterSergeant(): User
    {
        $division = $this->createActiveDivision();

        return $this->createMemberWithUser([
            'division_id' => $division->id,
            'rank'        => Rank::MASTER_SERGEANT,
        ]);
    }
}
