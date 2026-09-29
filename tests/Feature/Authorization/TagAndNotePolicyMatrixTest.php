<?php

namespace Tests\Feature\Authorization;

use App\Enums\TagVisibility;
use App\Models\DivisionTag;
use App\Models\Note;
use App\Policies\DivisionTagPolicy;
use PHPUnit\Framework\Attributes\Test;

class TagAndNotePolicyMatrixTest extends PermissionMatrixTestCase
{
    #[Test]
    public function division_tag_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $tags = [
            'division_a_public'         => [$this->world->divisionA->id, TagVisibility::PUBLIC],
            'division_a_officers'       => [$this->world->divisionA->id, TagVisibility::OFFICERS],
            'division_a_senior_leaders' => [$this->world->divisionA->id, TagVisibility::SENIOR_LEADERS],
            'division_b_public'         => [$this->world->divisionB->id, TagVisibility::PUBLIC],
            'global_public'             => [null, TagVisibility::PUBLIC],
        ];

        $checks = [
            ['viewAny', '(any tag)', fn () => [DivisionTag::class]],
            ['create', '(any tag)', fn ()   => [DivisionTag::class]],
            ['assign', '(no member)', fn () => [DivisionTag::class]],
        ];

        foreach ($tags as $name => [$divisionId, $visibility]) {
            $tag = DivisionTag::create(['name' => $name, 'division_id' => $divisionId, 'visibility' => $visibility]);

            foreach (['view', 'update', 'delete'] as $ability) {
                $checks[] = [$ability, $name, fn () => [$tag->fresh()]];
            }
        }

        foreach (PermissionWorld::TARGETS as $target) {
            $checks[] = ['assign', $target, fn (string $actor) => ($member = $this->world->target($actor, $target)) ? [DivisionTag::class, $member->fresh()] : null];
        }

        $lines = $this->evaluate(PermissionWorld::ACTORS, $checks);

        foreach (PermissionWorld::ACTORS as $actor) {
            $this->actAs($actor);

            $lines[] = self::line('getAssignableTags', '(tag names)', $actor, $this->outcomeList(
                fn () => (new DivisionTagPolicy)->getAssignableTags($this->world->users[$actor])->pluck('name')->sort()->values()->all()
            ));
        }

        $this->assertMatchesSnapshot('DivisionTagPolicy', $lines);
    }

    #[Test]
    public function note_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $author = $this->world->users['admin'];
        $notes  = [
            'same_squad misc'     => ['same_squad', 'misc'],
            'same_squad msgt'     => ['same_squad', 'msgt'],
            'same_squad sr_ldr'   => ['same_squad', 'sr_ldr'],
            'other_division misc' => ['other_division', 'misc'],
        ];

        $checks = [];

        foreach (['show', 'create', 'viewTrashed'] as $ability) {
            $checks[] = [$ability, '(any note)', fn () => [Note::class]];
        }

        foreach ($notes as $label => [$target, $type]) {
            $note = Note::create([
                'body'      => $label,
                'member_id' => $this->world->targets[$target]->id,
                'author_id' => $author->id,
                'type'      => $type,
            ]);

            foreach (['edit', 'delete', 'restore', 'forceDelete'] as $ability) {
                $checks[] = [$ability, $label, fn () => [$note->fresh()]];
            }
        }

        $this->assertMatchesSnapshot('NotePolicy', $this->evaluate(PermissionWorld::ACTORS, $checks));
    }

    private function outcomeList(callable $resolve): string
    {
        try {
            return '[' . implode(', ', $resolve()) . ']';
        } catch (\Throwable $e) {
            return 'error:' . class_basename($e);
        }
    }
}
