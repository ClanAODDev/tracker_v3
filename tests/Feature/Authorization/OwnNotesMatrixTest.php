<?php

namespace Tests\Feature\Authorization;

use App\Filament\Admin\Resources\NoteResource;
use App\Filament\Admin\Resources\NoteResource\Pages\CreateNote;
use App\Filament\Mod\Resources\LeaveResource\Pages\EditLeave;
use App\Filament\Mod\Resources\LeaveResource\RelationManagers\NoteRelationManager as LeaveNoteRelationManager;
use App\Filament\Mod\Resources\MemberResource\Pages\EditMember;
use App\Filament\Mod\Resources\MemberResource\RelationManagers\NotesRelationManager;
use App\Models\Leave;
use App\Models\Note;
use Filament\Facades\Filament;
use Livewire\Livewire;
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

            $before  = Note::where('member_id', $member->id)->count();
            $status  = $this->asActor($actor)->post(route('storeNote', $member->getUrlParams()), ['body' => 'about myself', 'type' => 'misc'])->status();
            $created = Note::where('member_id', $member->id)->count() > $before ? 'created' : 'not created';
            $lines[] = self::line('own notes', 'POST note on own profile', $actor, "{$status} {$created}");

            $this->actAs($actor);
            $user = $this->world->users[$actor];

            $lines[] = self::line('own notes', 'mod member notes tab shown', $actor, $user->canAccessPanel(Filament::getPanel('mod'))
                ? (NotesRelationManager::canViewForRecord($member, EditMember::class) ? 'yes' : 'no')
                : 'no panel access');

            $lines[] = self::line('own notes', 'admin note list includes it', $actor, $user->canAccessPanel(Filament::getPanel('admin'))
                ? (NoteResource::getEloquentQuery()->whereKey($note->id)->exists() ? 'yes' : 'no')
                : 'no panel access');

            $lines[] = self::line('own notes', 'admin note form saves a note about self', $actor, $user->canAccessPanel(Filament::getPanel('admin'))
                ? $this->adminFormCreates($member->id)
                : 'no panel access');

            $lines[] = self::line('own notes', 'admin note edit page for own note', $actor, $user->canAccessPanel(Filament::getPanel('admin'))
                ? $this->adminEditPageStatus($actor, $note)
                : 'no panel access');

            $leave   = Leave::factory()->create(['member_id' => $member->id]);
            $lines[] = self::line('own notes', 'leave note tab shown on own leave', $actor, $user->canAccessPanel(Filament::getPanel('mod'))
                ? (LeaveNoteRelationManager::canViewForRecord($leave, EditLeave::class) ? 'yes' : 'no')
                : 'no panel access');
            $leave->delete();

            $lines[] = self::line('own notes', "admin note list includes others' notes", $actor, $user->canAccessPanel(Filament::getPanel('admin'))
                ? (NoteResource::getEloquentQuery()->whereKey($otherNote->id)->exists() ? 'yes' : 'no')
                : 'no panel access');
        }

        $this->assertMatchesSnapshot('OwnNotes', $lines);
    }

    private function adminEditPageStatus(string $actor, Note $note): string
    {
        return (string) $this->asActor($actor)->get(NoteResource::getUrl('edit', ['record' => $note], panel: 'admin'))->status();
    }

    private function adminFormCreates(int $memberId): string
    {
        Filament::setCurrentPanel('admin');
        $before = Note::where('member_id', $memberId)->count();

        Livewire::test(CreateNote::class)
            ->fillForm(['body' => 'admin form note', 'type' => 'misc', 'member' => $memberId])
            ->call('create');

        return Note::where('member_id', $memberId)->count() > $before ? 'created' : 'not created';
    }
}
