<?php

namespace Tests\Feature\Controllers;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionNotesPageTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function it_renders_the_division_notes_inertia_page()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $officer->id,
            'type'      => 'positive',
        ]);

        $this->actingAs($officer)
            ->get(route('division.notes', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('division/notes')
                ->where('notes', fn ($notes) => collect($notes)->contains('id', $note->id))
                ->has('noteTypes')
                ->where('filters.type', null));
    }

    #[Test]
    public function it_filters_notes_by_type()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $positive = Note::factory()->create(['member_id' => $member->id, 'author_id' => $officer->id, 'type' => 'positive']);
        $negative = Note::factory()->create(['member_id' => $member->id, 'author_id' => $officer->id, 'type' => 'negative']);

        $this->actingAs($officer)
            ->get(route('division.notes', $division->slug) . '?type=positive')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('filters.type', 'positive')
                ->where('notes', fn ($notes) => collect($notes)->pluck('id')->all() === [$positive->id]));
    }
}
