<?php

namespace Tests\Feature\Authorization;

use App\Enums\ActivityType;
use App\Enums\TagVisibility;
use App\Filament\Mod\Resources\ActivityResource;
use App\Filament\Mod\Resources\DivisionResource;
use App\Filament\Mod\Resources\DivisionTagResource;
use App\Filament\Mod\Resources\LeaveResource;
use App\Filament\Mod\Resources\MemberAwardResource;
use App\Filament\Mod\Resources\MemberRequestResource;
use App\Filament\Mod\Resources\MemberResource;
use App\Filament\Mod\Resources\PlatoonResource;
use App\Filament\Mod\Resources\RankActionResource;
use App\Filament\Mod\Resources\SquadResource;
use App\Filament\Mod\Resources\TransferResource;
use App\Models\Activity;
use App\Models\Award;
use App\Models\DivisionTag;
use App\Models\MemberAward;
use App\Models\MemberRequest;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;

class FilamentResourceMatrixTest extends PermissionMatrixTestCase
{
    private const RESOURCES = [
        'Activity'      => ActivityResource::class,
        'Division'      => DivisionResource::class,
        'DivisionTag'   => DivisionTagResource::class,
        'Leave'         => LeaveResource::class,
        'MemberAward'   => MemberAwardResource::class,
        'MemberRequest' => MemberRequestResource::class,
        'Member'        => MemberResource::class,
        'Platoon'       => PlatoonResource::class,
        'RankAction'    => RankActionResource::class,
        'Squad'         => SquadResource::class,
        'Transfer'      => TransferResource::class,
    ];

    #[Test]
    public function mod_panel_resources_match_the_recorded_matrix(): void
    {
        $this->buildWorld();
        $this->seedScopedRecords();

        Filament::setCurrentPanel('mod');

        $lines = [];

        foreach (self::RESOURCES as $name => $resource) {
            foreach (['canAccess', 'canViewAny', 'canCreate'] as $method) {
                foreach (PermissionWorld::ACTORS as $actor) {
                    $this->actAs($actor);
                    $lines[] = self::line("{$name}Resource::{$method}", '-', $actor, $this->outcome(fn () => $resource::$method()));
                }
            }
        }

        $scoped = [
            'MemberAwardResource query'   => fn () => MemberAwardResource::getEloquentQuery()->with('award')->get()->pluck('award.name'),
            'MemberRequestResource query' => fn () => MemberRequestResource::getEloquentQuery()->get()->pluck('division.name'),
            'ActivityResource query'      => fn () => ActivityResource::getEloquentQuery()->get()->map(fn ($activity) => $activity->properties['label'] ?? '?'),
            'DivisionTagResource query'   => fn () => DivisionTagResource::getEloquentQuery()->pluck('name'),
        ];

        foreach ($scoped as $label => $query) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $this->actAs($actor);
                $lines[] = self::line($label, '(visible records)', $actor, $this->recordList($query));
            }
        }

        foreach (['canEdit', 'canDelete'] as $method) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $this->actAs($actor);
                $record  = MemberAward::first();
                $lines[] = self::line("MemberAwardResource::{$method}", 'any award', $actor, $this->outcome(fn () => MemberAwardResource::$method($record)));
            }
        }

        foreach (PermissionWorld::ACTORS as $actor) {
            $this->actAs($actor);
            $lines[] = self::line('MemberAwardResource::canDeleteAny', '-', $actor, $this->outcome(fn () => MemberAwardResource::canDeleteAny()));
        }

        $pages = [
            'GET member awards index' => fn () => route('filament.mod.resources.member-awards.index'),
            'GET member award edit'   => fn () => route('filament.mod.resources.member-awards.edit', MemberAward::first()),
        ];

        foreach ($pages as $label => $url) {
            foreach (PermissionWorld::ACTORS as $actor) {
                $lines[] = self::line($label, '(status)', $actor, (string) $this->asActor($actor)->get($url())->status());
            }
        }

        $this->assertMatchesSnapshot('FilamentModResources', $lines);
    }

    private function seedScopedRecords(): void
    {
        $awards = [
            'clan-wide award'  => null,
            'division A award' => $this->world->divisionA->id,
            'division B award' => $this->world->divisionB->id,
        ];

        foreach ($awards as $name => $divisionId) {
            $award = Award::factory()->create(['name' => $name, 'division_id' => $divisionId]);
            MemberAward::factory()->create([
                'award_id'     => $award->id,
                'member_id'    => $this->world->targets['same_squad']->clan_id,
                'requester_id' => $this->world->targets['higher_rank']->clan_id,
            ]);
        }

        foreach ([$this->world->divisionA, $this->world->divisionB] as $division) {
            MemberRequest::create([
                'member_id'    => $this->world->targets['same_squad']->id,
                'requester_id' => $this->world->targets['higher_rank']->id,
                'division_id'  => $division->id,
            ]);

            Activity::factory()->create([
                'name'        => ActivityType::RECRUITED,
                'division_id' => $division->id,
                'subject_id'  => $this->world->targets['same_squad']->id,
                'user_id'     => $this->world->users['admin']->id,
                'properties'  => ['label' => "{$division->name} activity"],
            ]);
        }

        $tags = [
            'division_a_public'         => [$this->world->divisionA->id, TagVisibility::PUBLIC],
            'division_a_officers'       => [$this->world->divisionA->id, TagVisibility::OFFICERS],
            'division_a_senior_leaders' => [$this->world->divisionA->id, TagVisibility::SENIOR_LEADERS],
            'division_b_public'         => [$this->world->divisionB->id, TagVisibility::PUBLIC],
            'global_public'             => [null, TagVisibility::PUBLIC],
        ];

        foreach ($tags as $name => [$divisionId, $visibility]) {
            DivisionTag::create(['name' => $name, 'division_id' => $divisionId, 'visibility' => $visibility]);
        }
    }

    private function recordList(callable $query): string
    {
        try {
            return '[' . collect($query())->sort()->values()->implode(', ') . ']';
        } catch (\Throwable $e) {
            return 'error:' . class_basename($e);
        }
    }
}
