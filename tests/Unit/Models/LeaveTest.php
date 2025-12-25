<?php

namespace Tests\Unit\Models;

use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class LeaveTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_expired_attribute_returns_true_for_past_end_date()
    {
        $member = $this->createMember();

        $leave = Leave::factory()->expired()->create([
            'member_id' => $member->clan_id,
        ]);

        $this->assertTrue($leave->expired);
    }

    public function test_expired_attribute_returns_false_for_future_end_date()
    {
        $member = $this->createMember();

        $leave = Leave::factory()->create([
            'member_id' => $member->clan_id,
            'end_date' => Carbon::now()->addDays(30),
        ]);

        $this->assertFalse($leave->expired);
    }

    public function test_date_attribute_returns_formatted_end_date()
    {
        $member = $this->createMember();
        $endDate = Carbon::parse('2025-06-15');

        $leave = Leave::factory()->create([
            'member_id' => $member->clan_id,
            'end_date' => $endDate,
        ]);

        $this->assertEquals('2025-06-15', $leave->date);
    }

    public function test_member_relationship_returns_correct_member()
    {
        $member = $this->createMember();

        $leave = Leave::factory()->create([
            'member_id' => $member->clan_id,
        ]);

        $this->assertEquals($member->clan_id, $leave->member->clan_id);
    }

    public function test_static_reasons_array_contains_expected_values()
    {
        $expectedReasons = ['military', 'medical', 'education', 'travel', 'other'];

        foreach ($expectedReasons as $reason) {
            $this->assertArrayHasKey($reason, Leave::$reasons);
        }
    }

    public function test_leave_factory_military_state()
    {
        $member = $this->createMember();

        $leave = Leave::factory()->military()->create([
            'member_id' => $member->clan_id,
        ]);

        $this->assertEquals('military', $leave->reason);
    }

    public function test_leave_factory_medical_state()
    {
        $member = $this->createMember();

        $leave = Leave::factory()->medical()->create([
            'member_id' => $member->clan_id,
        ]);

        $this->assertEquals('medical', $leave->reason);
    }
}
