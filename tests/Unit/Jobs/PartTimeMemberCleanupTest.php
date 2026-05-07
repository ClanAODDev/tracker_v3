<?php

namespace Tests\Unit\Jobs;

use App\Jobs\PartTimeMemberCleanup;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class PartTimeMemberCleanupTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function removes_part_time_entry_matching_full_time_division()
    {
        $division = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $division->id,
        ]);

        $member->partTimeDivisions()->attach($division->id);

        $this->assertCount(1, $member->partTimeDivisions);

        (new PartTimeMemberCleanup)->handle();

        $member->refresh();

        $this->assertCount(0, $member->partTimeDivisions);
    }

    #[Test]
    public function keeps_part_time_entries_for_different_divisions()
    {
        $fullTimeDivision = $this->createActiveDivision();
        $partTimeDivision = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $fullTimeDivision->id,
        ]);

        $member->partTimeDivisions()->attach($partTimeDivision->id);

        (new PartTimeMemberCleanup)->handle();

        $member->refresh();

        $this->assertCount(1, $member->partTimeDivisions);
        $this->assertEquals($partTimeDivision->id, $member->partTimeDivisions->first()->id);
    }

    #[Test]
    public function removes_only_matching_division_keeps_others()
    {
        $fullTimeDivision = $this->createActiveDivision();
        $partTimeDivision = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $fullTimeDivision->id,
        ]);

        $member->partTimeDivisions()->attach([
            $fullTimeDivision->id,
            $partTimeDivision->id,
        ]);

        $this->assertCount(2, $member->partTimeDivisions);

        (new PartTimeMemberCleanup)->handle();

        $member->refresh();

        $this->assertCount(1, $member->partTimeDivisions);
        $this->assertEquals($partTimeDivision->id, $member->partTimeDivisions->first()->id);
    }

    #[Test]
    public function does_not_affect_members_without_division()
    {
        $tempDivision     = $this->createActiveDivision();
        $partTimeDivision = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $tempDivision->id,
        ]);

        $member->partTimeDivisions()->attach($partTimeDivision->id);
        $member->update(['division_id' => 0]);

        (new PartTimeMemberCleanup)->handle();

        $member->refresh();

        $this->assertCount(1, $member->partTimeDivisions);
    }

    #[Test]
    public function does_not_affect_members_without_part_time_divisions()
    {
        $division = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $division->id,
        ]);

        (new PartTimeMemberCleanup)->handle();

        $member->refresh();

        $this->assertCount(0, $member->partTimeDivisions);
    }

    #[Test]
    public function job_is_queueable()
    {
        $job = new PartTimeMemberCleanup;

        $this->assertTrue(in_array(
            Queueable::class,
            class_uses_recursive($job)
        ));
    }
}
