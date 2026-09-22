<?php

namespace Tests\Feature\Controllers;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NoteSrLdrVisibilityTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function sr_ldr_role_user_can_create_sr_ldr_note(): void
    {
        $srLdr  = $this->createSeniorLeader();
        $member = $this->createMember(['division_id' => $srLdr->member->division_id]);

        $this->actingAs($srLdr)
            ->post(route('storeNote', $member->clan_id), [
                'body' => 'Visible only to Sr Leaders and above',
                'type' => 'sr_ldr',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'member_id' => $member->id,
            'type'      => 'sr_ldr',
        ]);
    }

    #[Test]
    public function non_sr_ldr_user_cannot_create_sr_ldr_note(): void
    {
        $officer = $this->createOfficer();
        $member  = $this->createMember(['division_id' => $officer->member->division_id]);

        $this->actingAs($officer)
            ->post(route('storeNote', $member->clan_id), [
                'body' => 'Should be rejected',
                'type' => 'sr_ldr',
            ])
            ->assertSessionHasErrors('type');

        $this->assertDatabaseMissing('notes', ['type' => 'sr_ldr']);
    }

    #[Test]
    public function sr_ldr_note_is_visible_on_profile_to_sr_ldr_viewer(): void
    {
        $srLdr  = $this->createSeniorLeader();
        $member = $this->createMember(['division_id' => $srLdr->member->division_id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $srLdr->id,
            'type'      => 'sr_ldr',
        ]);

        $this->actingAs($srLdr)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('notes', fn ($notes) => collect($notes)->contains('id', $note->id)));
    }

    #[Test]
    public function sr_ldr_note_is_hidden_on_profile_from_non_sr_ldr_viewer(): void
    {
        $srLdr  = $this->createSeniorLeader();
        $viewer = $this->createOfficer($srLdr->member->division);
        $member = $this->createMember(['division_id' => $srLdr->member->division_id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $srLdr->id,
            'type'      => 'sr_ldr',
        ]);

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('notes', fn ($notes) => ! collect($notes)->contains('id', $note->id)));
    }

    #[Test]
    public function admin_always_sees_sr_ldr_notes(): void
    {
        $srLdr  = $this->createSeniorLeader();
        $admin  = $this->createAdmin();
        $member = $this->createMember(['division_id' => $srLdr->member->division_id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $srLdr->id,
            'type'      => 'sr_ldr',
        ]);

        $this->actingAs($admin)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('notes', fn ($notes) => collect($notes)->contains('id', $note->id)));
    }

    #[Test]
    public function sr_ldr_note_is_hidden_from_division_notes_list_for_non_sr_ldr_viewer(): void
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $viewer   = $this->createOfficer($division);
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $srLdr->id,
            'type'      => 'sr_ldr',
        ]);

        $this->actingAs($viewer)
            ->get(route('division.notes', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('notes', fn ($notes) => ! collect($notes)->contains('id', $note->id)));
    }
}
