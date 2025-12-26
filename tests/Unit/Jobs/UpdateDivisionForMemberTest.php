<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UpdateDivisionForMember;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UpdateDivisionForMemberTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_job_can_be_instantiated()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->approved()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $job = new UpdateDivisionForMember($transfer);

        $this->assertInstanceOf(UpdateDivisionForMember::class, $job);
    }

    public function test_job_has_correct_procedure_constant()
    {
        $this->assertEquals('set_user_division', UpdateDivisionForMember::PROCEDURE_SET_DIVISION);
    }

    public function test_job_stores_transfer_reference()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->approved()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $job = new UpdateDivisionForMember($transfer);

        $this->assertEquals($transfer->id, $job->transfer->id);
    }

    public function test_job_accesses_member_clan_id_from_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $fromDivision->id,
            'clan_id' => 99999,
        ]);

        $transfer = Transfer::factory()->approved()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $job = new UpdateDivisionForMember($transfer);

        $this->assertEquals(99999, $job->transfer->member->clan_id);
    }

    public function test_job_accesses_division_name_from_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision(['name' => 'Target Division']);
        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->approved()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $job = new UpdateDivisionForMember($transfer);

        $this->assertEquals('Target Division', $job->transfer->division->name);
    }

    public function test_job_is_queueable()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->approved()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $job = new UpdateDivisionForMember($transfer);

        $this->assertTrue(in_array(
            \Illuminate\Foundation\Queue\Queueable::class,
            class_uses_recursive($job)
        ));
    }
}
