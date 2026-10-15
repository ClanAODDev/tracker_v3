<?php

namespace Tests\Feature\Authorization;

use App\Enums\Rank;
use App\Enums\TagVisibility;
use App\Models\Award;
use App\Models\Division;
use App\Models\DivisionTag;
use App\Models\Member;
use App\Models\Note;
use App\Models\RankAction;
use PHPUnit\Framework\Attributes\Test;

class ModelScopeMatrixTest extends PermissionMatrixTestCase
{
    #[Test]
    public function model_scopes_and_helpers_match_the_recorded_matrix(): void
    {
        $this->buildWorld();
        $this->seedRankActions();
        $this->seedTags();
        $awards = $this->seedAwards();

        $lines = [];

        foreach (PermissionWorld::ACTORS as $actor) {
            $user = $this->world->users[$actor];
            $this->actAs($actor);

            $lines[] = self::line('Member::eligibleForRankAction', '(member names)', $actor, $this->names(fn () => Member::eligibleForRankAction($user)->pluck('name')));
            $lines[] = self::line('RankAction::forUser', '(rank actions)', $actor, $this->names(fn () => RankAction::forUser($user)->pluck('justification')));
            $lines[] = self::line('DivisionTag::visibleTo', '(tag names)', $actor, $this->names(fn () => DivisionTag::visibleTo($user)->pluck('name')));
            $lines[] = self::line('DivisionTag::assignableBy', '(tag names)', $actor, $this->names(fn () => DivisionTag::assignableBy($user)->pluck('name')));
            $lines[] = self::line('Note::canManageSrLdr', '-', $actor, $this->outcome(fn () => Note::canManageSrLdr($user)));
            $lines[] = self::line('Note::canManageMsgt', '-', $actor, $this->outcome(fn () => Note::canManageMsgt($user)));

            foreach ($awards as $label => $award) {
                $lines[] = self::line('Award::canBeRequestedBy', $label, $actor, $this->outcome(fn () => $award->fresh()->canBeRequestedBy($user)));
            }
        }

        $this->assertMatchesSnapshot('ModelScopes', $lines);
    }

    private function seedRankActions(): void
    {
        $requester = $this->world->targets['higher_rank'];

        foreach (['same_squad', 'same_platoon_other_squad', 'other_platoon', 'other_division'] as $target) {
            foreach (['private_first_class' => Rank::PRIVATE_FIRST_CLASS, 'specialist' => Rank::SPECIALIST, 'sergeant' => Rank::SERGEANT] as $label => $rank) {
                RankAction::create([
                    'member_id'     => $this->world->targets[$target]->id,
                    'requester_id'  => $requester->id,
                    'rank'          => $rank,
                    'justification' => "{$target} to {$label}",
                ]);
            }
        }

        foreach (PermissionWorld::ACTORS as $actor) {
            $member = $this->world->users[$actor]->member;

            if (! $member) {
                continue;
            }

            RankAction::create([
                'member_id'     => $this->world->targets['same_squad']->id,
                'requester_id'  => $member->id,
                'rank'          => Rank::SPECIALIST,
                'justification' => "requested by {$actor}",
            ]);
        }
    }

    private function seedTags(): void
    {
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

    private function seedAwards(): array
    {
        $inactive = Division::factory()->withoutHandles()->create(['name' => 'Inactive Division', 'active' => false]);

        return [
            'requestable award'         => Award::factory()->create(['division_id' => $this->world->divisionA->id, 'allow_request' => true]),
            'non-requestable award'     => Award::factory()->create(['division_id' => $this->world->divisionA->id, 'allow_request' => false]),
            'inactive-division award'   => Award::factory()->create(['division_id' => $inactive->id, 'allow_request' => true]),
            'clan-wide non-requestable' => Award::factory()->create(['division_id' => null, 'allow_request' => false]),
        ];
    }

    private function names(callable $resolve): string
    {
        try {
            return '[' . collect($resolve())->sort()->values()->implode(', ') . ']';
        } catch (\Throwable $e) {
            return 'error:' . class_basename($e);
        }
    }
}
