<?php

namespace Tests\Feature\Controllers;

use App\Enums\Position;
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

class NoteMsgtVisibilityTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function createLeaderWithRank(Rank $rank): User
    {
        $division = $this->createActiveDivision();

        return $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::EXECUTIVE_OFFICER,
            'rank'        => $rank,
        ], [
            'role' => Role::OFFICER,
        ]);
    }

    #[Test]
    public function msgt_rank_user_can_create_an_msgt_note(): void
    {
        $leader = $this->createLeaderWithRank(Rank::MASTER_SERGEANT);
        $member = $this->createMember(['division_id' => $leader->member->division_id]);

        $this->actingAs($leader)
            ->post(route('storeNote', $member->clan_id), [
                'body' => 'Visible only to MSGT and above',
                'type' => 'msgt',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'member_id' => $member->id,
            'type'      => 'msgt',
        ]);
    }

    #[Test]
    public function below_msgt_rank_user_cannot_create_an_msgt_note(): void
    {
        $leader = $this->createLeaderWithRank(Rank::STAFF_SERGEANT);
        $member = $this->createMember(['division_id' => $leader->member->division_id]);

        $this->actingAs($leader)
            ->post(route('storeNote', $member->clan_id), [
                'body' => 'Should be rejected',
                'type' => 'msgt',
            ])
            ->assertSessionHasErrors('type');

        $this->assertDatabaseMissing('notes', ['type' => 'msgt']);
    }

    #[Test]
    public function msgt_note_is_visible_on_profile_to_msgt_rank_viewer(): void
    {
        $author = $this->createLeaderWithRank(Rank::MASTER_SERGEANT);
        $member = $this->createMember(['division_id' => $author->member->division_id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $author->id,
            'type'      => 'msgt',
        ]);

        $this->actingAs($author)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('notes', fn ($notes) => collect($notes)->contains('id', $note->id)));
    }

    #[Test]
    public function msgt_note_is_hidden_on_profile_from_below_msgt_viewer(): void
    {
        $author = $this->createLeaderWithRank(Rank::MASTER_SERGEANT);
        $viewer = $this->createLeaderWithRank(Rank::STAFF_SERGEANT);
        $member = $this->createMember(['division_id' => $author->member->division_id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $author->id,
            'type'      => 'msgt',
        ]);

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('notes', fn ($notes) => ! collect($notes)->contains('id', $note->id)));
    }

    #[Test]
    public function admin_always_sees_msgt_notes(): void
    {
        $author = $this->createLeaderWithRank(Rank::MASTER_SERGEANT);
        $admin  = $this->createAdmin();
        $member = $this->createMember(['division_id' => $author->member->division_id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $author->id,
            'type'      => 'msgt',
        ]);

        $this->actingAs($admin)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('notes', fn ($notes) => collect($notes)->contains('id', $note->id)));
    }

    #[Test]
    public function below_msgt_division_leader_cannot_delete_an_msgt_note(): void
    {
        $author = $this->createLeaderWithRank(Rank::MASTER_SERGEANT);
        $viewer = $this->createLeaderWithRank(Rank::STAFF_SERGEANT);
        $member = $this->createMember(['division_id' => $viewer->member->division_id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $author->id,
            'type'      => 'msgt',
        ]);

        $this->actingAs($viewer)
            ->delete(route('deleteNote', [$member->clan_id, $note->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('notes', ['id' => $note->id, 'deleted_at' => null]);
    }

    #[Test]
    public function msgt_rank_division_leader_can_delete_an_msgt_note(): void
    {
        $author = $this->createLeaderWithRank(Rank::MASTER_SERGEANT);
        $member = $this->createMember(['division_id' => $author->member->division_id]);

        $note = Note::factory()->create([
            'member_id' => $member->id,
            'author_id' => $author->id,
            'type'      => 'msgt',
        ]);

        $this->actingAs($author)
            ->delete(route('deleteNote', [$member->clan_id, $note->id]))
            ->assertRedirect();

        $this->assertSoftDeleted($note);
    }
}
