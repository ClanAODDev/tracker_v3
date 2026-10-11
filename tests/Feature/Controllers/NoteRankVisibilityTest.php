<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Enums\Role;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NoteRankVisibilityTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function viewer(Rank $rank, Role $role = Role::OFFICER): User
    {
        return $this->createMemberWithUser(['rank' => $rank], ['role' => $role]);
    }

    private function noteAbout(Rank $rank, ?User $author = null, string $type = 'misc'): Note
    {
        return Note::factory()->create([
            'member_id' => $this->createMember(['rank' => $rank])->id,
            'author_id' => ($author ?? $this->viewer(Rank::SERGEANT_MAJOR, Role::ADMIN))->id,
            'type'      => $type,
        ]);
    }

    #[Test]
    public function a_viewer_only_sees_notes_about_strictly_lower_ranks(): void
    {
        $viewer = $this->viewer(Rank::STAFF_SERGEANT);

        $lower = $this->noteAbout(Rank::SERGEANT);
        $same  = $this->noteAbout(Rank::STAFF_SERGEANT);
        $above = $this->noteAbout(Rank::MASTER_SERGEANT);

        $visible = Note::visibleTo($viewer)->pluck('id')->all();

        $this->assertContains($lower->id, $visible);
        $this->assertNotContains($same->id, $visible);
        $this->assertNotContains($above->id, $visible);
    }

    #[Test]
    public function a_viewer_still_sees_notes_they_authored_after_the_subject_is_promoted(): void
    {
        $viewer = $this->viewer(Rank::CORPORAL);
        $note   = $this->noteAbout(Rank::SERGEANT, $viewer);

        $this->assertContains($note->id, Note::visibleTo($viewer)->pluck('id')->all());
    }

    #[Test]
    public function a_viewer_without_a_member_only_sees_their_own_notes(): void
    {
        $viewer = User::factory()->create(['member_id' => null, 'role' => Role::OFFICER]);
        $own    = $this->noteAbout(Rank::PRIVATE, $viewer);
        $other  = $this->noteAbout(Rank::PRIVATE);

        $visible = Note::visibleTo($viewer)->pluck('id')->all();

        $this->assertSame([$own->id], $visible);
        $this->assertNotContains($other->id, $visible);
    }

    #[Test]
    public function admins_see_notes_regardless_of_rank(): void
    {
        $admin = $this->viewer(Rank::PRIVATE, Role::ADMIN);
        $note  = $this->noteAbout(Rank::SERGEANT_MAJOR);

        $this->assertContains($note->id, Note::visibleTo($admin)->pluck('id')->all());
    }

    #[Test]
    public function the_master_sergeant_note_type_still_requires_the_type_gate_on_top_of_rank(): void
    {
        $viewer = $this->viewer(Rank::FIRST_SERGEANT);
        $msgt   = $this->noteAbout(Rank::PRIVATE, type: 'msgt');

        $this->assertContains($msgt->id, Note::visibleTo($viewer)->pluck('id')->all());

        $staff = $this->viewer(Rank::STAFF_SERGEANT);

        $this->assertNotContains($msgt->id, Note::visibleTo($staff)->pluck('id')->all());
    }

    #[Test]
    public function notes_about_a_master_sergeant_are_only_visible_to_first_sergeants_and_above(): void
    {
        $note = $this->noteAbout(Rank::MASTER_SERGEANT);

        $this->assertNotContains($note->id, Note::visibleTo($this->viewer(Rank::MASTER_SERGEANT))->pluck('id')->all());
        $this->assertContains($note->id, Note::visibleTo($this->viewer(Rank::FIRST_SERGEANT))->pluck('id')->all());
    }

    #[Test]
    public function the_profile_hides_notes_once_the_subject_reaches_the_viewers_rank(): void
    {
        $viewer  = $this->viewer(Rank::STAFF_SERGEANT);
        $subject = $this->createMember(['rank' => Rank::SERGEANT]);
        $note    = Note::factory()->create(['member_id' => $subject->id, 'author_id' => $this->viewer(Rank::SERGEANT_MAJOR, Role::ADMIN)->id]);

        $this->actingAs($viewer)
            ->get(route('member', $subject->getUrlParams()))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canCreateNote', true)
                ->where('canAddNote', true)
                ->where('notes', fn ($notes) => collect($notes)->contains('id', $note->id)));

        $subject->update(['rank' => Rank::STAFF_SERGEANT]);

        $this->actingAs($viewer)
            ->get(route('member', $subject->getUrlParams()))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canCreateNote', false)
                ->where('canAddNote', false)
                ->where('notes', fn ($notes) => collect($notes)->isEmpty()));
    }

    #[Test]
    public function an_author_keeps_view_access_but_cannot_add_or_edit_after_the_subject_outranks_them(): void
    {
        $viewer  = $this->viewer(Rank::STAFF_SERGEANT);
        $subject = $this->createMember(['rank' => Rank::MASTER_SERGEANT]);
        $note    = Note::factory()->create(['member_id' => $subject->id, 'author_id' => $viewer->id]);

        $this->assertTrue($viewer->can('viewForMember', [Note::class, $subject]));
        $this->assertFalse($viewer->can('createForMember', [Note::class, $subject]));
        $this->assertFalse($viewer->can('edit', $note));
        $this->assertFalse($viewer->can('delete', $note));

        $this->actingAs($viewer)
            ->get(route('member', $subject->getUrlParams()))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canAddNote', false)
                ->where('notes', fn ($notes) => collect($notes)->pluck('id')->all() === [$note->id]));
    }

    #[Test]
    public function an_equal_rank_viewer_who_never_wrote_a_note_cannot_view_or_create(): void
    {
        $viewer  = $this->viewer(Rank::SERGEANT);
        $subject = $this->createMember(['rank' => Rank::SERGEANT]);

        $this->assertFalse($viewer->can('viewForMember', [Note::class, $subject]));
        $this->assertFalse($viewer->can('createForMember', [Note::class, $subject]));

        $this->actingAs($viewer)
            ->post(route('storeNote', $subject->clan_id), ['body' => 'Nope', 'type' => 'misc'])
            ->assertForbidden();
    }

    #[Test]
    public function trashed_notes_follow_the_same_rank_rule(): void
    {
        $viewer = $this->viewer(Rank::STAFF_SERGEANT);
        $lower  = $this->noteAbout(Rank::SERGEANT);
        $above  = $this->noteAbout(Rank::MASTER_SERGEANT);

        $lower->delete();
        $above->delete();

        $visible = Note::onlyTrashed()->visibleTo($viewer)->pluck('id')->all();

        $this->assertSame([$lower->id], $visible);
    }

    #[Test]
    public function the_division_notes_page_omits_notes_about_members_at_or_above_the_viewer(): void
    {
        $viewer   = $this->createOfficer(memberAttributes: ['rank' => Rank::STAFF_SERGEANT]);
        $division = $viewer->member->division;
        $author   = $this->viewer(Rank::SERGEANT_MAJOR, Role::ADMIN);

        $lower = Note::factory()->create([
            'member_id' => $this->createMember(['division_id' => $division->id, 'rank' => Rank::SERGEANT])->id,
            'author_id' => $author->id,
        ]);
        $same = Note::factory()->create([
            'member_id' => $this->createMember(['division_id' => $division->id, 'rank' => Rank::STAFF_SERGEANT])->id,
            'author_id' => $author->id,
        ]);

        $this->actingAs($viewer)
            ->get(route('division.notes', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('notes', fn ($notes) => collect($notes)->pluck('id')->all() === [$lower->id]));
    }

    #[Test]
    public function editing_deleting_and_restoring_require_outranking_the_subject(): void
    {
        $leader = $this->createOfficer(memberAttributes: ['rank' => Rank::STAFF_SERGEANT]);
        $lower  = Note::factory()->create(['member_id' => $this->createMember(['rank' => Rank::SERGEANT])->id, 'author_id' => $leader->id]);
        $same   = Note::factory()->create(['member_id' => $this->createMember(['rank' => Rank::STAFF_SERGEANT])->id, 'author_id' => $leader->id]);

        foreach (['edit', 'delete', 'restore', 'forceDelete'] as $ability) {
            $this->assertTrue($leader->can($ability, $lower), $ability);
            $this->assertFalse($leader->can($ability, $same), $ability);
        }
    }
}
