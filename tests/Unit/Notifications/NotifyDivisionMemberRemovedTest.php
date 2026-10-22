<?php

namespace Tests\Unit\Notifications;

use App\Models\DivisionUnitLevel;
use App\Models\Unit;
use App\Notifications\Channel\NotifyDivisionMemberRemoved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NotifyDivisionMemberRemovedTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function unitField(NotifyDivisionMemberRemoved $notification, $division): ?string
    {
        $fields = $notification->toBot($division)['body']['embeds'][0]['fields'];

        return collect($fields)->firstWhere('name', 'Unit')['value'] ?? null;
    }

    #[Test]
    public function the_unit_field_shows_the_full_trail_at_any_depth(): void
    {
        $division = $this->createActiveDivision();
        $division->settings()->set('chat_alerts.member_removed', 'officers');
        DivisionUnitLevel::create(['division_id' => $division->id, 'depth' => 3, 'label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader']);

        $platoon = Unit::factory()->create(['division_id' => $division->id, 'name' => 'Alpha Company']);
        $middle  = Unit::factory()->childOf($platoon)->create(['name' => 'First Platoon']);
        $team    = Unit::factory()->childOf($middle)->create(['name' => 'Red Team']);
        $member  = $this->createMember(['division_id' => $division->id, 'unit_id' => $team->id]);

        $notification = new NotifyDivisionMemberRemoved($member, null, null, $team->fresh());

        $this->assertSame('Alpha Company / First Platoon / Red Team', $this->unitField($notification, $division));
    }

    #[Test]
    public function a_top_level_unit_shows_alone_and_no_unit_shows_unassigned(): void
    {
        $division = $this->createActiveDivision();
        $division->settings()->set('chat_alerts.member_removed', 'officers');
        $platoon = Unit::factory()->create(['division_id' => $division->id, 'name' => 'Alpha Company']);
        $member  = $this->createMember(['division_id' => $division->id, 'unit_id' => $platoon->id]);

        $this->assertSame('Alpha Company', $this->unitField(new NotifyDivisionMemberRemoved($member, null, null, $platoon), $division));
        $this->assertSame('Unassigned', $this->unitField(new NotifyDivisionMemberRemoved($member, null, null), $division));
    }
}
