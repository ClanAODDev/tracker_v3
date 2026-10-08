<?php

namespace Tests\Feature\Authorization;

use App\Enums\Rank;
use App\Enums\TagVisibility;
use App\Filament\Mod\Resources\ActivityResource\Pages\ListActivities;
use App\Filament\Mod\Resources\DivisionResource;
use App\Filament\Mod\Resources\DivisionTagResource\Pages\ListDivisionTags;
use App\Filament\Mod\Resources\MemberResource\Pages\ListMembers;
use App\Filament\Mod\Resources\PlatoonResource\Pages\EditPlatoon;
use App\Filament\Mod\Resources\PlatoonResource\RelationManagers\MembersRelationManager;
use App\Filament\Mod\Resources\RankActionResource\Pages\EditRankAction;
use App\Filament\Mod\Resources\RankActionResource\Pages\ListRankActions;
use App\Filament\Mod\Resources\TransferResource\Pages\ListTransfers;
use App\Models\DivisionTag;
use App\Models\Member;
use App\Models\RankAction;
use App\Models\Unit;
use Filament\Actions\Exceptions\ActionNotResolvableException;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

class FilamentVisibilityMatrixTest extends PermissionMatrixTestCase
{
    #[Test]
    public function mod_panel_visibility_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();
        Filament::setCurrentPanel('mod');

        $globalTag  = DivisionTag::create(['name' => 'global_public', 'division_id' => null, 'visibility' => TagVisibility::PUBLIC]);
        $rankAction = RankAction::create([
            'member_id'    => $this->world->targets['same_squad']->id,
            'requester_id' => $this->world->targets['higher_rank']->id,
            'rank'         => Rank::SPECIALIST,
        ]);
        $awaitingAcceptance = RankAction::create([
            'member_id'    => $this->world->targets['same_platoon_other_squad']->id,
            'requester_id' => $this->world->targets['higher_rank']->id,
            'rank'         => Rank::SPECIALIST,
            'approved_at'  => now()->subDays(2),
        ]);

        $checks = [
            'ListMembers unit filter: division field'  => fn () => Livewire::test(ListMembers::class)->assertFormFieldVisible('unit.division', 'tableFiltersForm'),
            'ListMembers member_scope filter'          => fn () => Livewire::test(ListMembers::class)->assertTableFilterVisible('member_scope'),
            'ListMembers member_transfer bulk action'  => fn () => Livewire::test(ListMembers::class)->assertTableBulkActionVisible('member_transfer'),
            'ListRankActions division column'          => fn () => Livewire::test(ListRankActions::class)->assertTableColumnVisible('member.division.name'),
            'ListActivities division column'           => fn () => Livewire::test(ListActivities::class)->assertTableColumnVisible('division.name'),
            'ListActivities division filter'           => fn () => Livewire::test(ListActivities::class)->assertTableFilterVisible('division_id'),
            'ListDivisionTags delete on global tag'    => fn () => Livewire::test(ListDivisionTags::class)->assertTableActionVisible('delete', $globalTag),
            'ListTransfers delete bulk action'         => fn () => Livewire::test(ListTransfers::class)->assertTableBulkActionVisible('delete'),
            'EditRankAction cancel action'             => fn () => $this->assertHeaderActionVisible($rankAction, 'delete'),
            'EditRankAction requeue action'            => fn () => $this->assertHeaderActionVisible($awaitingAcceptance, 'requeue'),
            'Platoon A1 members: transfer bulk action' => fn () => Livewire::test(MembersRelationManager::class, [
                'ownerRecord' => $this->world->platoons['A1'],
                'pageClass'   => EditPlatoon::class,
            ])->assertTableBulkActionVisible('member_transfer'),
        ];

        $lines = [];

        foreach ($checks as $label => $assertVisible) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $this->actAs($actor);
                $lines[] = self::line($label, '-', $actor, $this->visibility($assertVisible));
            }
        }

        foreach (PermissionWorld::ACTORS as $actor) {
            $this->actAs($actor);
            $lines[] = self::line('DivisionResource::shouldRegisterNavigation', '-', $actor, $this->outcome(fn () => DivisionResource::shouldRegisterNavigation()));
            $lines[] = self::line('ListMembers default filtered records', '(names)', $actor, $this->listOf(fn () => Livewire::test(ListMembers::class)->instance()->getFilteredTableQuery()->pluck('name')));
            $lines[] = self::line('ListActivities performed-by options', '(count)', $actor, $this->listOf(fn () => [Livewire::test(ListActivities::class)->instance()->getTable()->getFilter('user_id')->getOptions() ? 'has options' : 'none']));
        }

        $this->assertMatchesSnapshot('FilamentVisibility', $lines);
    }

    #[Test]
    public function mod_panel_division_pages_match_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $pages = [
            'GET divisions index'     => fn () => DivisionResource::getUrl('index', panel: 'mod'),
            'GET edit own division'   => fn () => DivisionResource::getUrl('edit', ['record' => $this->world->divisionA], panel: 'mod'),
            'GET edit other division' => fn () => DivisionResource::getUrl('edit', ['record' => $this->world->divisionB], panel: 'mod'),
        ];

        $lines = [];

        foreach ($pages as $label => $url) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $response = $this->asActor($actor)->get($url());
                $target   = $response->isRedirect() ? ' -> ' . parse_url($response->headers->get('Location'), PHP_URL_PATH) : '';
                $lines[]  = self::line($label, '(status)', $actor, $response->status() . $target);
            }
        }

        $this->assertMatchesSnapshot('FilamentDivisionPages', $lines);
    }

    #[Test]
    public function member_reassignment_bulk_action_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();
        Filament::setCurrentPanel('mod');

        $platoonB2 = Unit::factory()->create(['division_id' => $this->world->divisionB->id, 'name' => 'B2', 'leader_id' => null]);

        $cases = [
            'own division member'   => ['same_squad', $this->world->platoons['A2']],
            'other division member' => ['other_division', $platoonB2],
        ];

        $lines = [];

        foreach ($cases as $label => [$target, $destination]) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $member   = $this->world->targets[$target]->fresh();
                $original = $member->unit_id;
                $this->actAs($actor);

                $outcome = $this->outcome(function () use ($member, $destination, $original) {
                    Livewire::test(ListMembers::class)
                        ->callTableBulkAction('member_transfer', [$member], data: ['platoon_id' => $destination->id, 'squad_id' => null]);

                    return $member->fresh()->unit_id !== $original;
                });

                Member::whereKey($member->id)->update(['unit_id' => $original]);
                $lines[] = self::line('member_transfer bulk action', $label, $actor, match (true) {
                    $outcome === 'allow'                                 => 'moved',
                    $outcome === 'deny'                                  => 'not moved',
                    str_starts_with($outcome, 'error:ExpectationFailed') => 'action unavailable',
                    default                                              => $outcome,
                });
            }
        }

        $this->assertMatchesSnapshot('FilamentMemberReassignment', $lines);
    }

    private function assertHeaderActionVisible(RankAction $record, string $name): void
    {
        $action = Livewire::test(EditRankAction::class, ['record' => $record->getRouteKey()])->instance()->getAction($name);

        $this->assertTrue((bool) $action?->isVisible());
    }

    private function visibility(callable $assertVisible): string
    {
        try {
            $assertVisible();

            return 'visible';
        } catch (AssertionFailedError) {
            return 'hidden';
        } catch (ActionNotResolvableException) {
            return 'record not listed';
        } catch (Throwable $e) {
            return str_contains($e->getMessage(), 'on null') ? 'page not accessible' : 'error:' . class_basename($e);
        }
    }

    private function listOf(callable $resolve): string
    {
        try {
            return '[' . collect($resolve())->sort()->values()->implode(', ') . ']';
        } catch (Throwable $e) {
            return str_contains($e->getMessage(), 'on null') ? 'page not accessible' : 'error:' . class_basename($e);
        }
    }
}
