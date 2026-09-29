<?php

namespace Tests\Unit\Services;

use App\Models\Handle;
use App\Models\MemberHandle;
use App\Services\MemberHandleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberHandleServiceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private MemberHandleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MemberHandleService;
    }

    #[Test]
    public function sync_creates_updates_and_deletes_rows()
    {
        $handle  = Handle::factory()->create();
        $member  = $this->createMember();
        $keep    = MemberHandle::create(['member_id' => $member->id, 'handle_id' => $handle->id, 'value' => 'old', 'primary' => true]);
        $removed = MemberHandle::create(['member_id' => $member->id, 'handle_id' => $handle->id, 'value' => 'gone', 'primary' => false]);

        $this->service->sync($member, [
            ['id' => $keep->id, 'handle_id' => $handle->id, 'value' => 'renamed', 'primary' => true],
            ['id' => null, 'handle_id' => $handle->id, 'value' => 'added', 'primary' => false],
        ]);

        $this->assertDatabaseHas('handle_member', ['id' => $keep->id, 'value' => 'renamed', 'primary' => true]);
        $this->assertDatabaseHas('handle_member', ['member_id' => $member->id, 'value' => 'added', 'primary' => false]);
        $this->assertDatabaseMissing('handle_member', ['id' => $removed->id]);
    }

    #[Test]
    public function sync_makes_the_last_row_of_a_type_primary_when_none_is_flagged()
    {
        $handle = Handle::factory()->create();
        $member = $this->createMember();

        $this->service->sync($member, [
            ['handle_id' => $handle->id, 'value' => 'first', 'primary' => false],
            ['handle_id' => $handle->id, 'value' => 'second', 'primary' => false],
        ]);

        $this->assertDatabaseHas('handle_member', ['member_id' => $member->id, 'value' => 'first', 'primary' => false]);
        $this->assertDatabaseHas('handle_member', ['member_id' => $member->id, 'value' => 'second', 'primary' => true]);
    }

    #[Test]
    public function sync_cannot_modify_another_members_handle_row()
    {
        $handle   = Handle::factory()->create();
        $member   = $this->createMember();
        $victim   = $this->createMember();
        $theirRow = MemberHandle::create(['member_id' => $victim->id, 'handle_id' => $handle->id, 'value' => 'theirs', 'primary' => true]);

        $this->service->sync($member, [
            ['id' => $theirRow->id, 'handle_id' => $handle->id, 'value' => 'hijacked', 'primary' => true],
        ]);

        $this->assertDatabaseHas('handle_member', ['id' => $theirRow->id, 'member_id' => $victim->id, 'value' => 'theirs']);
    }

    #[Test]
    public function sync_normalizes_steam_values()
    {
        $steam  = Handle::factory()->create(['type' => Handle::STEAM_PROFILE]);
        $member = $this->createMember();

        $this->service->sync($member, [
            ['handle_id' => $steam->id, 'value' => 'https://steamcommunity.com/profiles/76561197968443902/', 'primary' => true],
        ]);

        $this->assertDatabaseHas('handle_member', ['member_id' => $member->id, 'value' => '76561197968443902']);
    }

    #[Test]
    public function set_primary_keeps_the_previous_value_as_an_alternate()
    {
        $handle = Handle::factory()->create();
        $member = $this->createMember();
        MemberHandle::create(['member_id' => $member->id, 'handle_id' => $handle->id, 'value' => 'old', 'primary' => true]);

        $this->service->setPrimary($member, $handle, 'new');

        $this->assertDatabaseHas('handle_member', ['member_id' => $member->id, 'value' => 'old', 'primary' => false]);
        $this->assertDatabaseHas('handle_member', ['member_id' => $member->id, 'value' => 'new', 'primary' => true]);
    }

    #[Test]
    public function set_primary_promotes_an_existing_matching_value_instead_of_duplicating_it()
    {
        $handle = Handle::factory()->create();
        $member = $this->createMember();
        MemberHandle::create(['member_id' => $member->id, 'handle_id' => $handle->id, 'value' => 'main', 'primary' => true]);
        MemberHandle::create(['member_id' => $member->id, 'handle_id' => $handle->id, 'value' => 'alt', 'primary' => false]);

        $this->service->setPrimary($member, $handle, 'alt');

        $this->assertSame(2, MemberHandle::where('member_id', $member->id)->count());
        $this->assertDatabaseHas('handle_member', ['member_id' => $member->id, 'value' => 'alt', 'primary' => true]);
        $this->assertDatabaseHas('handle_member', ['member_id' => $member->id, 'value' => 'main', 'primary' => false]);
    }

    #[Test]
    public function set_for_division_stores_filled_values_for_division_types_only()
    {
        $na       = Handle::factory()->create();
        $eu       = Handle::factory()->create();
        $other    = Handle::factory()->create();
        $division = $this->createDivisionWithHandles([$na, $eu]);
        $member   = $this->createMember();

        $this->service->setForDivision($member, $division, [$na->id => 'na_name', $eu->id => '', $other->id => 'ignored']);

        $this->assertDatabaseHas('handle_member', ['member_id' => $member->id, 'handle_id' => $na->id, 'value' => 'na_name']);
        $this->assertDatabaseMissing('handle_member', ['member_id' => $member->id, 'handle_id' => $eu->id]);
        $this->assertDatabaseMissing('handle_member', ['member_id' => $member->id, 'handle_id' => $other->id]);
    }
}
