<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class OwnProfileNoteVisibilityTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function noteAbout(User $user, string $type): Note
    {
        $author = $this->createAdmin();

        return Note::create(['body' => "{$type} note", 'member_id' => $user->member_id, 'author_id' => $author->id, 'type' => $type]);
    }

    private function visibleNoteIds(User $user): array
    {
        $response = $this->actingAs($user)->get(route('member', $user->member->getUrlParams()))->assertOk();

        return collect($response->viewData('page')['props']['notes'])->pluck('id')->sort()->values()->all();
    }

    #[Test]
    public function an_officer_sees_notes_about_themselves_except_sr_leader_and_msgt_types(): void
    {
        $officer = $this->createOfficer($this->createActiveDivision(), ['rank' => Rank::CORPORAL]);

        $misc = $this->noteAbout($officer, 'misc');
        $this->noteAbout($officer, 'sr_ldr');
        $this->noteAbout($officer, 'msgt');

        $this->assertSame([$misc->id], $this->visibleNoteIds($officer));
    }

    #[Test]
    public function a_senior_leader_sees_the_sr_leader_type_but_needs_master_sergeant_for_msgt(): void
    {
        $division = $this->createActiveDivision();

        $sergeant = $this->createSeniorLeader($division, ['rank' => Rank::SERGEANT]);
        $misc     = $this->noteAbout($sergeant, 'misc');
        $srLdr    = $this->noteAbout($sergeant, 'sr_ldr');
        $this->noteAbout($sergeant, 'msgt');

        $this->assertSame([$misc->id, $srLdr->id], $this->visibleNoteIds($sergeant));

        $master = $this->createSeniorLeader($division, ['rank' => Rank::MASTER_SERGEANT]);
        $ids    = collect([$this->noteAbout($master, 'misc'), $this->noteAbout($master, 'sr_ldr'), $this->noteAbout($master, 'msgt')])->pluck('id')->sort()->values()->all();

        $this->assertSame($ids, $this->visibleNoteIds($master));
    }

    #[Test]
    public function officers_still_cannot_add_edit_or_delete_notes_about_themselves(): void
    {
        $officer = $this->createOfficer($this->createActiveDivision());
        $note    = $this->noteAbout($officer, 'misc');

        $response = $this->actingAs($officer)->get(route('member', $officer->member->getUrlParams()))->assertOk();
        $this->assertFalse($response->viewData('page')['props']['canAddNote']);

        $this->assertFalse($officer->can('createForMember', [Note::class, $officer->member]));
        $this->assertFalse($officer->can('edit', $note));
        $this->assertFalse($officer->can('delete', $note));
        $this->assertFalse($officer->can('restore', $note));
    }
}
