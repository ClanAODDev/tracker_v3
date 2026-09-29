<?php

namespace Tests\Feature\Migrations;

use App\Models\Handle;
use App\Models\MemberHandle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class RefileEuWarshipsHandlesTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    private Handle $na;

    private Handle $eu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->na = Handle::factory()->create(['type' => 'warships_na', 'label' => 'Warships NA']);
        $this->eu = Handle::factory()->create(['type' => 'warships_eu', 'label' => 'Warships EU']);
    }

    #[Test]
    public function an_eu_id_stored_as_na_moves_to_the_eu_handle_type()
    {
        $member = $this->createMember();
        $row    = $this->handle($member, $this->na, '529112229');

        $this->runMigration();

        $this->assertDatabaseHas('handle_member', ['id' => $row->id, 'handle_id' => $this->eu->id, 'value' => '529112229', 'primary' => true]);
    }

    #[Test]
    public function an_eu_id_already_stored_as_eu_drops_the_na_duplicate()
    {
        $member = $this->createMember();
        $naCopy = $this->handle($member, $this->na, '529112229');
        $euCopy = $this->handle($member, $this->eu, '529112229');

        $this->runMigration();

        $this->assertDatabaseMissing('handle_member', ['id' => $naCopy->id]);
        $this->assertDatabaseHas('handle_member', ['id' => $euCopy->id, 'handle_id' => $this->eu->id]);
    }

    #[Test]
    public function a_moved_id_is_an_alternate_when_the_member_already_has_a_different_eu_id()
    {
        $member = $this->createMember();
        $moved  = $this->handle($member, $this->na, '529112229');
        $this->handle($member, $this->eu, '501720057');

        $this->runMigration();

        $this->assertDatabaseHas('handle_member', ['id' => $moved->id, 'handle_id' => $this->eu->id, 'primary' => false]);
    }

    #[Test]
    public function na_ids_names_and_other_numbers_are_left_alone()
    {
        $member = $this->createMember();
        $naId   = $this->handle($member, $this->na, '1019888099');
        $name   = $this->handle($member, $this->na, 'DrunkCanadian', primary: false);
        $short  = $this->handle($member, $this->na, '004198672', primary: false);

        $this->runMigration();

        foreach ([$naId, $name, $short] as $row) {
            $this->assertDatabaseHas('handle_member', ['id' => $row->id, 'handle_id' => $this->na->id, 'value' => $row->value]);
        }
    }

    #[Test]
    public function the_members_remaining_na_handle_becomes_primary_when_the_primary_one_moved()
    {
        $member = $this->createMember();
        $this->handle($member, $this->na, '529112229');
        $remaining = $this->handle($member, $this->na, '1019888099', primary: false);

        $this->runMigration();

        $this->assertDatabaseHas('handle_member', ['id' => $remaining->id, 'primary' => true]);
    }

    private function handle($member, Handle $handle, string $value, bool $primary = true): MemberHandle
    {
        return MemberHandle::create([
            'member_id' => $member->id,
            'handle_id' => $handle->id,
            'value'     => $value,
            'primary'   => $primary,
        ]);
    }

    private function runMigration(): void
    {
        (require database_path('migrations/2026_09_29_140000_refile_eu_ids_stored_as_warships_na_handles.php'))->up();
    }
}
