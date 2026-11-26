<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\MemberResource as AdminMemberResource;
use App\Filament\Mod\Resources\MemberResource as ModMemberResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberResourceRecordUrlTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function edit_urls_use_the_member_id(): void
    {
        $member = $this->createMember();

        $this->assertStringEndsWith(
            "/operations/members/{$member->id}/edit",
            ModMemberResource::getUrl('edit', ['record' => $member], panel: 'mod'),
        );
        $this->assertStringEndsWith(
            "/admin/members/{$member->id}/edit",
            AdminMemberResource::getUrl('edit', ['record' => $member], panel: 'admin'),
        );
    }

    #[Test]
    public function the_edit_page_opens_by_id(): void
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($this->createAdmin())
            ->get(ModMemberResource::getUrl('edit', ['record' => $member], panel: 'mod'))
            ->assertOk()
            ->assertSee($member->name);
    }

    #[Test]
    public function a_guid_does_not_resolve_to_another_member(): void
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $guid     = $member->id . 'abcdefghi';

        $this->assertNull(ModMemberResource::resolveRecordRouteBinding($guid));
        $this->assertNull(AdminMemberResource::resolveRecordRouteBinding($guid));

        $this->actingAs($this->createAdmin())
            ->get("/operations/members/{$guid}/edit")
            ->assertNotFound();
    }
}
