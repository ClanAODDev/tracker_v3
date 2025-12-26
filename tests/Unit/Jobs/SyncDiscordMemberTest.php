<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncDiscordMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class SyncDiscordMemberTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_job_can_be_instantiated()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $division->id]);

        $job = new SyncDiscordMember($member);

        $this->assertInstanceOf(SyncDiscordMember::class, $job);
    }

    public function test_job_stores_member_reference()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $division->id]);

        $job = new SyncDiscordMember($member);

        $this->assertEquals($member->id, $job->member->id);
    }

    public function test_job_is_queueable()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $division->id]);

        $job = new SyncDiscordMember($member);

        $this->assertTrue(in_array(
            \Illuminate\Foundation\Queue\Queueable::class,
            class_uses_recursive($job)
        ));
    }

    public function test_member_discord_fields_can_be_updated()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'discord_id' => null,
            'discord' => null,
        ]);

        $member->discord_id = '123456789012345678';
        $member->discord = 'TestUser#1234';
        $member->save();

        $member->refresh();
        $this->assertEquals('123456789012345678', $member->discord_id);
        $this->assertEquals('TestUser#1234', $member->discord);
    }

    public function test_member_is_dirty_when_discord_fields_change()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'discord_id' => 'old_id',
            'discord' => 'old_tag',
        ]);

        $member->discord_id = 'new_id';
        $member->discord = 'new_tag';

        $this->assertTrue($member->isDirty());
    }

    public function test_member_is_not_dirty_when_no_changes()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'discord_id' => 'same_id',
            'discord' => 'same_tag',
        ]);

        $member->refresh();

        $this->assertFalse($member->isDirty());
    }
}
