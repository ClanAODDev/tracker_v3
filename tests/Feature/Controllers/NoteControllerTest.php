<?php

namespace Tests\Feature\Controllers;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NoteControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_sr_ldr_can_restore_soft_deleted_note()
    {
        $srLdr = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

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
            'id' => $note->id,
            'deleted_at' => null,
        ]);
    }

    public function test_sr_ldr_can_force_delete_soft_deleted_note()
    {
        $srLdr = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

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

    public function test_officer_cannot_restore_soft_deleted_note()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $officer->id,
        ]);
        $note->delete();

        $response = $this->actingAs($officer)
            ->postJson(route('restoreNote', [$member->clan_id, $note->id]));

        $response->assertForbidden();
    }

    public function test_officer_cannot_force_delete_soft_deleted_note()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $officer->id,
        ]);
        $note->delete();

        $response = $this->actingAs($officer)
            ->deleteJson(route('forceDeleteNote', [$member->clan_id, $note->id]));

        $response->assertForbidden();
    }

    public function test_restore_returns_404_for_nonexistent_note()
    {
        $srLdr = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        $response = $this->actingAs($srLdr)
            ->postJson(route('restoreNote', [$member->clan_id, 99999]));

        $response->assertNotFound();
    }

    public function test_force_delete_returns_404_for_nonexistent_note()
    {
        $srLdr = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        $response = $this->actingAs($srLdr)
            ->deleteJson(route('forceDeleteNote', [$member->clan_id, 99999]));

        $response->assertNotFound();
    }

    public function test_cannot_restore_note_belonging_to_different_member()
    {
        $srLdr = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member1 = $this->createMember(['division_id' => $division->id]);
        $member2 = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member1->id,
            'author_id' => $srLdr->id,
        ]);
        $note->delete();

        $response = $this->actingAs($srLdr)
            ->postJson(route('restoreNote', [$member2->clan_id, $note->id]));

        $response->assertNotFound();
    }

    public function test_admin_can_restore_soft_deleted_note()
    {
        $admin = $this->createAdmin();
        $division = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $division->id]);

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

    public function test_admin_can_force_delete_soft_deleted_note()
    {
        $admin = $this->createAdmin();
        $division = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $division->id]);

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
}
