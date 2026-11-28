<?php

namespace Tests\Feature\Filament;

use App\Enums\Ability;
use App\Enums\Rank;
use App\Enums\Role;
use App\Filament\Mod\Resources\RankActionResource\Pages\ImportRankHistory;
use App\Jobs\UpdateRankForMember;
use App\Models\RankAction;
use App\Models\User;
use App\Models\UserAbility;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
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
    public function admins_can_access_the_import_page(): void
    {
        $this->actingAs($this->createImporter())
            ->get(route('filament.mod.resources.rank-actions.import-history'))
            ->assertOk();
    }

    #[Test]
    public function non_admins_cannot_access_the_import_page_even_at_master_sergeant(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id, 'rank' => Rank::MASTER_SERGEANT], ['role' => Role::SENIOR_LEADER]);

        $this->actingAs($user)
            ->get(route('filament.mod.resources.rank-actions.import-history'))
            ->assertForbidden();
    }

    #[Test]
    public function a_non_admin_granted_import_history_can_access_the_import_page(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id, 'rank' => Rank::SERGEANT], ['role' => Role::SENIOR_LEADER]);
        UserAbility::create(['user_id' => $user->id, 'ability' => Ability::ImportHistory->value, 'reason' => 'test']);

        $this->actingAs($user)
            ->get(route('filament.mod.resources.rank-actions.import-history'))
            ->assertOk();
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
        $user   = $this->createImporter();
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
    public function import_rejects_dates_after_yesterday(): void
    {
        $user   = $this->createImporter();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        $this->actingAs($user);

        Livewire::test(ImportRankHistory::class)
            ->set('data.member_id', $target->id)
            ->set('data.entries', [
                ['rank' => (string) Rank::PRIVATE->value, 'date' => now()->toDateString()],
            ])
            ->call('create')
            ->assertHasErrors();

        $this->assertDatabaseCount('rank_actions', 0);
    }

    #[Test]
    public function pasted_rows_dated_after_yesterday_are_skipped(): void
    {
        $user   = $this->createImporter();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        $this->actingAs($user);

        $entries = Livewire::test(ImportRankHistory::class)
            ->set('data.member_id', $target->id)
            ->set('data.csv_paste', "Private,2020-01-01\nSpecialist," . now()->addDay()->toDateString())
            ->callAction(TestAction::make('addPastedRows')->schemaComponent('history_paste_actions'))
            ->get('data.entries');

        $this->assertSame(['2020-01-01'], collect($entries)->pluck('date')->values()->all());
    }

    #[Test]
    public function import_does_not_change_the_members_current_rank(): void
    {
        Bus::fake();

        $user   = $this->createImporter();
        $target = $this->createMember([
            'division_id' => $user->member->division_id,
            'rank'        => Rank::PRIVATE,
        ]);

        $this->actingAs($user);

        Livewire::test(ImportRankHistory::class)
            ->set('data.member_id', $target->id)
            ->set('data.entries', [
                ['rank' => (string) Rank::SERGEANT->value, 'date' => '2021-05-01'],
            ])
            ->call('create');

        $this->assertDatabaseCount('rank_actions', 1);
        $this->assertSame(Rank::PRIVATE, $target->fresh()->rank);
        Bus::assertNotDispatched(UpdateRankForMember::class);
    }

    #[Test]
    public function import_sets_historical_dates_on_approved_and_accepted(): void
    {
        $user   = $this->createImporter();
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
        $this->assertEquals('2021-06-15', $action->created_at->toDateString());
    }

    #[Test]
    public function import_sets_awarded_at_for_officer_ranks(): void
    {
        $user   = $this->createImporter();
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
        $user   = $this->createImporter();
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
        $user   = $this->createImporter();
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
        $user   = $this->createImporter();
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
        $user   = $this->createImporter();
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

    #[Test]
    public function import_skips_entries_that_collide_on_date(): void
    {
        $user   = $this->createImporter();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        RankAction::create([
            'member_id'     => $target->id,
            'requester_id'  => $user->member_id,
            'approver_id'   => $user->member_id,
            'rank'          => Rank::PRIVATE->value,
            'approved_at'   => '2020-03-01',
            'accepted_at'   => '2020-03-01',
            'justification' => 'Historical entry',
        ]);

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
    public function csv_paste_merges_with_existing_entries_sorted_by_date(): void
    {
        $user   = $this->createImporter();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        $this->actingAs($user);

        $component = Livewire::test(ImportRankHistory::class)
            ->set('data.member_id', $target->id)
            ->set('data.entries', [
                ['rank' => (string) Rank::PRIVATE->value, 'date' => '2020-01-01'],
                ['rank' => (string) Rank::CORPORAL->value, 'date' => '2022-06-01'],
            ])
            ->set('data.csv_paste', 'Specialist,2021-03-15')
            ->callAction(TestAction::make('addPastedRows')->schemaComponent('history_paste_actions'));

        $entries = $component->get('data.entries');

        $this->assertCount(3, $entries);
        $this->assertEquals('2020-01-01', $entries[0]['date']);
        $this->assertEquals('2021-03-15', $entries[1]['date']);
        $this->assertEquals('2022-06-01', $entries[2]['date']);
    }

    #[Test]
    public function csv_paste_into_empty_entries_populates_sorted_by_date(): void
    {
        $user = $this->createImporter();

        $this->actingAs($user);

        $component = Livewire::test(ImportRankHistory::class)
            ->set('data.csv_paste', "Corporal,2021-06-01\nPrivate,2020-01-01")
            ->callAction(TestAction::make('addPastedRows')->schemaComponent('history_paste_actions'));

        $entries = $component->get('data.entries');

        $this->assertCount(2, $entries);
        $this->assertEquals('2020-01-01', $entries[0]['date']);
        $this->assertEquals('2021-06-01', $entries[1]['date']);
    }

    #[Test]
    public function typing_in_the_paste_box_does_not_add_rows_until_requested(): void
    {
        $this->actingAs($this->createImporter());

        $component = Livewire::test(ImportRankHistory::class)
            ->set('data.csv_paste', 'Priv')
            ->set('data.csv_paste', 'Private,2020-0')
            ->set('data.csv_paste', 'Private,2020-01-01');

        $this->assertEmpty(collect($component->get('data.entries'))->filter(fn ($entry) => filled($entry['rank'] ?? null)));
        $component->assertNotNotified();
    }

    #[Test]
    public function adding_pasted_rows_skips_rows_already_listed_and_clears_the_box(): void
    {
        $this->actingAs($this->createImporter());

        $component = Livewire::test(ImportRankHistory::class)
            ->set('data.entries', [['rank' => (string) Rank::PRIVATE->value, 'date' => '2020-01-01']])
            ->set('data.csv_paste', "rank,date\nPrivate,2020-01-01\nCorporal,2021-06-01")
            ->callAction(TestAction::make('addPastedRows')->schemaComponent('history_paste_actions'));

        $this->assertSame(['2020-01-01', '2021-06-01'], collect($component->get('data.entries'))->pluck('date')->all());
        $this->assertEmpty($component->get('data.csv_paste'));
        $component->assertNotified('Added 1 row');
    }

    protected function createImporter(): User
    {
        $division = $this->createActiveDivision();

        return $this->createMemberWithUser(
            ['division_id' => $division->id, 'rank' => Rank::MASTER_SERGEANT],
            ['role' => Role::ADMIN],
        );
    }
}
