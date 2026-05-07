<?php

namespace Tests\Feature\Controllers;

use App\Enums\Position;
use App\Enums\Role;
use App\Models\DivisionTag;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NoteControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function sr_ldr_can_restore_soft_deleted_note()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $srLdr->id,
        ]);
        $note->delete();

        $this->assertSoftDeleted('notes', ['id' => $note->id]);

        $response = $this->actingAs($srLdr)
            ->postJson(route('restoreNote', [$member->clan_id, $note->id]));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('notes', [
            'id'         => $note->id,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function sr_ldr_can_force_delete_soft_deleted_note()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $srLdr->id,
        ]);
        $note->delete();

        $response = $this->actingAs($srLdr)
            ->deleteJson(route('forceDeleteNote', [$member->clan_id, $note->id]));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    #[Test]
    public function division_leader_can_restore_soft_deleted_note()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $officer->id,
        ]);
        $note->delete();

        $response = $this->actingAs($officer)
            ->postJson(route('restoreNote', [$member->clan_id, $note->id]));

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    #[Test]
    public function division_leader_can_force_delete_soft_deleted_note()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $officer->id,
        ]);
        $note->delete();

        $response = $this->actingAs($officer)
            ->deleteJson(route('forceDeleteNote', [$member->clan_id, $note->id]));

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    #[Test]
    public function regular_user_cannot_restore_soft_deleted_note()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $user->id,
        ]);
        $note->delete();

        $response = $this->actingAs($user)
            ->postJson(route('restoreNote', [$member->clan_id, $note->id]));

        $response->assertForbidden();
    }

    #[Test]
    public function regular_user_cannot_force_delete_soft_deleted_note()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $user->id,
        ]);
        $note->delete();

        $response = $this->actingAs($user)
            ->deleteJson(route('forceDeleteNote', [$member->clan_id, $note->id]));

        $response->assertForbidden();
    }

    #[Test]
    public function restore_returns_404_for_nonexistent_note()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $response = $this->actingAs($srLdr)
            ->postJson(route('restoreNote', [$member->clan_id, 99999]));

        $response->assertNotFound();
    }

    #[Test]
    public function force_delete_returns_404_for_nonexistent_note()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $response = $this->actingAs($srLdr)
            ->deleteJson(route('forceDeleteNote', [$member->clan_id, 99999]));

        $response->assertNotFound();
    }

    #[Test]
    public function cannot_restore_note_belonging_to_different_member()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member1  = $this->createMember(['division_id' => $division->id]);
        $member2  = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member1->id,
            'author_id' => $srLdr->id,
        ]);
        $note->delete();

        $response = $this->actingAs($srLdr)
            ->postJson(route('restoreNote', [$member2->clan_id, $note->id]));

        $response->assertNotFound();
    }

    #[Test]
    public function admin_can_restore_soft_deleted_note()
    {
        $admin    = $this->createAdmin();
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $admin->id,
        ]);
        $note->delete();

        $response = $this->actingAs($admin)
            ->postJson(route('restoreNote', [$member->clan_id, $note->id]));

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    #[Test]
    public function admin_can_force_delete_soft_deleted_note()
    {
        $admin    = $this->createAdmin();
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $admin->id,
        ]);
        $note->delete();

        $response = $this->actingAs($admin)
            ->deleteJson(route('forceDeleteNote', [$member->clan_id, $note->id]));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    #[Test]
    public function division_leader_can_soft_delete_note()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $officer->id,
        ]);

        $response = $this->actingAs($officer)
            ->delete(route('deleteNote', [$member->clan_id, $note->id]));

        $response->assertRedirect();
        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    #[Test]
    public function sr_ldr_can_soft_delete_note()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $srLdr->id,
        ]);

        $response = $this->actingAs($srLdr)
            ->delete(route('deleteNote', [$member->clan_id, $note->id]));

        $response->assertRedirect();
        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    #[Test]
    public function regular_user_cannot_soft_delete_note()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('deleteNote', [$member->clan_id, $note->id]));

        $response->assertForbidden();
        $this->assertDatabaseHas('notes', [
            'id'         => $note->id,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function admin_can_soft_delete_note()
    {
        $admin    = $this->createAdmin();
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('deleteNote', [$member->clan_id, $note->id]));

        $response->assertRedirect();
        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    #[Test]
    public function can_create_note_with_tag()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $tag = DivisionTag::factory()->create(['division_id' => $division->id]);

        $response = $this->actingAs($srLdr)
            ->post(route('storeNote', $member->clan_id), [
                'body'   => 'Test note with tag',
                'type'   => 'misc',
                'tag_id' => $tag->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'member_id' => $member->id,
            'body'      => 'Test note with tag',
        ]);

        $this->assertDatabaseHas('member_tag', [
            'member_id'       => $member->id,
            'division_tag_id' => $tag->id,
        ]);
    }

    #[Test]
    public function can_create_note_without_tag()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $response = $this->actingAs($srLdr)
            ->post(route('storeNote', $member->clan_id), [
                'body' => 'Test note without tag',
                'type' => 'misc',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'member_id' => $member->id,
            'body'      => 'Test note without tag',
        ]);
    }

    #[Test]
    public function platoon_leader_can_create_note_with_tag()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::PLATOON_LEADER,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        $tag = DivisionTag::factory()->create(['division_id' => $division->id]);

        $response = $this->actingAs($user)
            ->post(route('storeNote', $member->clan_id), [
                'body'   => 'Test note from platoon leader',
                'type'   => 'misc',
                'tag_id' => $tag->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('member_tag', [
            'member_id'       => $member->id,
            'division_tag_id' => $tag->id,
        ]);
    }

    #[Test]
    public function officer_can_assign_tag_when_creating_note()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::MEMBER,
        ], [
            'role' => Role::OFFICER,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        $tag = DivisionTag::factory()->create(['division_id' => $division->id]);

        $response = $this->actingAs($user)
            ->post(route('storeNote', $member->clan_id), [
                'body'   => 'Test note from officer',
                'type'   => 'misc',
                'tag_id' => $tag->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'member_id' => $member->id,
            'body'      => 'Test note from officer',
        ]);

        $this->assertDatabaseHas('member_tag', [
            'member_id'       => $member->id,
            'division_tag_id' => $tag->id,
        ]);
    }

    #[Test]
    public function cannot_assign_tag_from_different_division()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $otherDivision = $this->createActiveDivision();
        $tag           = DivisionTag::factory()->create(['division_id' => $otherDivision->id]);

        $response = $this->actingAs($srLdr)
            ->post(route('storeNote', $member->clan_id), [
                'body'   => 'Test note with wrong division tag',
                'type'   => 'misc',
                'tag_id' => $tag->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'member_id' => $member->id,
            'body'      => 'Test note with wrong division tag',
        ]);

        $this->assertDatabaseMissing('member_tag', [
            'member_id'       => $member->id,
            'division_tag_id' => $tag->id,
        ]);
    }

    #[Test]
    public function can_assign_global_tag_when_creating_note()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $globalTag = DivisionTag::factory()->global()->create();

        $response = $this->actingAs($srLdr)
            ->post(route('storeNote', $member->clan_id), [
                'body'   => 'Test note with global tag',
                'type'   => 'misc',
                'tag_id' => $globalTag->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('member_tag', [
            'member_id'       => $member->id,
            'division_tag_id' => $globalTag->id,
        ]);
    }
}
