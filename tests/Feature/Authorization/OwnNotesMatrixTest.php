<?php

namespace Tests\Feature\Authorization;

use App\Filament\Admin\Resources\NoteResource;
use App\Filament\Mod\Resources\MemberResource\Pages\EditMember;
use App\Filament\Mod\Resources\MemberResource\RelationManagers\NotesRelationManager;
use App\Models\Note;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;

class OwnNotesMatrixTest extends PermissionMatrixTestCase
{
    #[Test]
    public function notes_on_the_actors_own_profile_match_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $author    = $this->world->users['admin'];
        $lines     = [];
        $otherNote = Note::create([
            'body'      => 'note about someone else',
            'member_id' => $this->world->targets['other_division']->id,
            'author_id' => $author->id,
            'type'      => 'misc',
        ]);

        foreach (PermissionWorld::ACTORS as $actor) {
            $member = $this->world->target($actor, 'self');

            if (! $member) {
                $lines[] = self::line('own notes', '(all surfaces)', $actor, 'n/a');

                continue;
            }

            $note = Note::create([
                'body'      => "{$actor} own note",
                'member_id' => $member->id,
                'author_id' => $author->id,
                'type'      => 'misc',
            ]);

            $response = $this->asActor($actor)->get(route('member', $member->getUrlParams()));
            $props    = $response->status() === 200 ? $response->viewData('page')['props'] : null;
            $lines[]  = self::line('own notes', 'profile notes listed', $actor, $props ? (string) collect($props['notes'] ?? [])->count() : (string) $response->status());
            $lines[]  = self::line('own notes', 'profile note count in stats', $actor, $props ? (string) ($props['stats']['notes']['total'] ?? 'missing') : (string) $response->status());

            $response = $this->asActor($actor)->get(route('division.notes', $member->division->slug));
            $listed   = $response->status() === 200 ? collect($response->viewData('page')['props']['notes'])->pluck('id')->contains($note->id) : null;
            $lines[]  = self::line('own notes', 'division notes page lists it', $actor, $listed === null ? (string) $response->status() : ($listed ? 'yes' : 'no'));

            $this->actAs($actor);
            $user = $this->world->users[$actor];

            $lines[] = self::line('own notes', 'mod member notes tab shown', $actor, $user->canAccessPanel(Filament::getPanel('mod'))
                ? (NotesRelationManager::canViewForRecord($member, EditMember::class) ? 'yes' : 'no')
                : 'no panel access');

            $lines[] = self::line('own notes', 'admin note list includes it', $actor, $user->canAccessPanel(Filament::getPanel('admin'))
                ? (NoteResource::getEloquentQuery()->whereKey($note->id)->exists() ? 'yes' : 'no')
                : 'no panel access');

            $lines[] = self::line('own notes', "admin note list includes others' notes", $actor, $user->canAccessPanel(Filament::getPanel('admin'))
                ? (NoteResource::getEloquentQuery()->whereKey($otherNote->id)->exists() ? 'yes' : 'no')
                : 'no panel access');
        }

        $this->assertMatchesSnapshot('OwnNotes', $lines);
    }
}
