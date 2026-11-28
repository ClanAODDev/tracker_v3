<?php

namespace Tests\Feature\Filament;

use App\Enums\Rank;
use App\Enums\Role;
use App\Filament\Mod\Resources\TransferResource\Pages\ImportTransferHistory;
use App\Models\Transfer;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ImportTransferHistoryTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function msgt_can_access_the_import_page(): void
    {
        $this->actingAs($this->createMasterSergeant())
            ->get(route('filament.mod.resources.transfers.import-history'))
            ->assertOk();
    }

    #[Test]
    public function below_msgt_cannot_access_the_import_page(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id, 'rank' => Rank::STAFF_SERGEANT]);

        $this->actingAs($user)
            ->get(route('filament.mod.resources.transfers.import-history'))
            ->assertForbidden();
    }

    #[Test]
    public function import_records_approved_transfers_on_their_historical_dates(): void
    {
        Bus::fake();
        Notification::fake();

        $user    = $this->createMasterSergeant();
        $current = $user->member->division;
        $past    = $this->createActiveDivision(['name' => 'World of Warships']);
        $target  = $this->createMember(['division_id' => $current->id]);

        $this->actingAs($user);

        Livewire::test(ImportTransferHistory::class)
            ->fillForm([
                'member_id' => $target->id,
                'entries'   => [['division' => (string) $past->id, 'date' => '2021-07-15']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $transfer = Transfer::where('member_id', $target->id)->sole();

        $this->assertSame($past->id, $transfer->division_id);
        $this->assertSame('2021-07-15', $transfer->created_at->toDateString());
        $this->assertStringStartsWith('2021-07-15', (string) $transfer->approved_at);
        $this->assertSame($user->id, $transfer->approved_by);
        $this->assertSame($current->id, $target->fresh()->division_id);
        Bus::assertNothingDispatched();
        Notification::assertNothingSent();
    }

    #[Test]
    public function import_skips_transfers_already_on_record(): void
    {
        $user   = $this->createMasterSergeant();
        $past   = $this->createActiveDivision();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        Transfer::withoutTimestamps(fn () => Transfer::create([
            'member_id' => $target->id, 'division_id' => $past->id, 'approved_at' => '2020-01-01', 'created_at' => '2020-01-01', 'updated_at' => '2020-01-01',
        ]));

        $this->actingAs($user);

        Livewire::test(ImportTransferHistory::class)
            ->fillForm([
                'member_id' => $target->id,
                'entries'   => [
                    ['division' => (string) $past->id, 'date' => '2020-01-01'],
                    ['division' => (string) $past->id, 'date' => '2022-02-02'],
                ],
            ])
            ->call('create');

        $this->assertSame(2, Transfer::where('member_id', $target->id)->count());
    }

    #[Test]
    public function choosing_a_member_lists_their_existing_transfers(): void
    {
        $user   = $this->createMasterSergeant();
        $past   = $this->createActiveDivision();
        $target = $this->createMember(['division_id' => $user->member->division_id]);

        Transfer::withoutTimestamps(fn () => Transfer::create([
            'member_id' => $target->id, 'division_id' => $past->id, 'approved_at' => '2020-01-01', 'created_at' => '2020-01-01', 'updated_at' => '2020-01-01',
        ]));
        Transfer::create(['member_id' => $target->id, 'division_id' => $past->id]);

        $this->actingAs($user);

        $entries = Livewire::test(ImportTransferHistory::class)
            ->set('data.member_id', $target->id)
            ->get('data.entries');

        $this->assertSame([['division' => (string) $past->id, 'date' => '2020-01-01']], array_values($entries));
    }

    #[Test]
    public function pasted_rows_match_divisions_by_name_abbreviation_or_slug(): void
    {
        $user = $this->createMasterSergeant();
        $wows = $this->createActiveDivision(['name' => 'World of Warships', 'abbreviation' => 'wows']);
        $bf   = $this->createActiveDivision(['name' => 'Battlefield', 'abbreviation' => 'bf']);

        $this->actingAs($user);

        $component = Livewire::test(ImportTransferHistory::class)
            ->set('data.csv_paste', "division,date\nworld of warships,2021-07-15\nBF,2019-03-01\n{$wows->slug},2022-01-01\nNope,2020-01-01\nBattlefield," . now()->addDay()->toDateString())
            ->callAction(TestAction::make('addPastedRows')->schemaComponent('history_paste_actions'))
            ->assertNotified('2 rows skipped');

        $this->assertSame(
            [[(string) $bf->id, '2019-03-01'], [(string) $wows->id, '2021-07-15'], [(string) $wows->id, '2022-01-01']],
            collect($component->get('data.entries'))->filter(fn ($entry) => filled($entry['division'] ?? null))->map(fn ($entry) => [$entry['division'], $entry['date']])->values()->all(),
        );
    }

    #[Test]
    public function the_template_downloads_as_csv(): void
    {
        $this->actingAs($this->createMasterSergeant())
            ->get(route('transfer-history.template'))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="transfer-history-template.csv"');
    }

    protected function createMasterSergeant(): User
    {
        $division = $this->createActiveDivision();

        return $this->createMemberWithUser(
            ['division_id' => $division->id, 'rank' => Rank::MASTER_SERGEANT],
            ['role' => Role::OFFICER],
        );
    }
}
