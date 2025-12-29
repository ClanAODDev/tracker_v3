<?php

namespace Tests\Feature;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Division;
use App\Models\Member;
use App\Models\Note;
use App\Models\Platoon;
use App\Models\Squad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ActivityLoggingTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_member_activity_records_correct_type_and_subject()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer);
        $member->recordActivity(ActivityType::RECRUITED);

        $activity = Activity::where('name', ActivityType::RECRUITED)->first();

        $this->assertNotNull($activity);
        $this->assertEquals($member->id, $activity->subject_id);
        $this->assertEquals(Member::class, $activity->subject_type);
        $this->assertEquals($officer->id, $activity->user_id);
        $this->assertEquals($division->id, $activity->division_id);
    }

    public function test_activity_records_properties()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $platoon = Platoon::factory()->create(['division_id' => $division->id]);
        $member = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer);
        $member->recordActivity(ActivityType::ASSIGNED_PLATOON, [
            'platoon' => $platoon->name,
        ]);

        $activity = Activity::where('name', ActivityType::ASSIGNED_PLATOON)->first();

        $this->assertNotNull($activity);
        $this->assertIsArray($activity->properties);
        $this->assertEquals($platoon->name, $activity->properties['platoon']);
    }

    public function test_note_creation_logs_activity_on_member()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer);

        Note::create([
            'body' => 'Test note',
            'member_id' => $member->id,
            'author_id' => $officer->id,
            'type' => 'positive',
        ]);

        $activity = Activity::where('name', ActivityType::CREATED_NOTE)->first();

        $this->assertNotNull($activity);
        $this->assertEquals($member->id, $activity->subject_id);
        $this->assertEquals(Member::class, $activity->subject_type);
        $this->assertEquals('positive', $activity->properties['type']);
    }

    public function test_note_update_logs_activity_on_member()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer);

        $note = Note::create([
            'body' => 'Test note',
            'member_id' => $member->id,
            'author_id' => $officer->id,
            'type' => 'misc',
        ]);

        Activity::truncate();

        $note->update(['body' => 'Updated note']);

        $activity = Activity::where('name', ActivityType::UPDATED_NOTE)->first();

        $this->assertNotNull($activity);
        $this->assertEquals($member->id, $activity->subject_id);
    }

    public function test_note_deletion_logs_activity_on_member()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer);

        $note = Note::create([
            'body' => 'Test note',
            'member_id' => $member->id,
            'author_id' => $officer->id,
            'type' => 'negative',
        ]);

        Activity::truncate();

        $note->delete();

        $activity = Activity::where('name', ActivityType::DELETED_NOTE)->first();

        $this->assertNotNull($activity);
        $this->assertEquals($member->id, $activity->subject_id);
        $this->assertEquals('negative', $activity->properties['type']);
    }

    public function test_squad_creation_logs_activity()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $platoon = Platoon::factory()->create(['division_id' => $division->id]);

        $this->actingAs($officer);

        $squad = Squad::create([
            'name' => 'Alpha Squad',
            'platoon_id' => $platoon->id,
        ]);

        $activity = Activity::where('name', ActivityType::CREATED_SQUAD)->first();

        $this->assertNotNull($activity);
        $this->assertEquals($squad->id, $activity->subject_id);
        $this->assertEquals(Squad::class, $activity->subject_type);
    }

    public function test_platoon_creation_logs_activity()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;

        $this->actingAs($officer);

        $platoon = Platoon::create([
            'name' => 'Bravo Platoon',
            'division_id' => $division->id,
        ]);

        $activity = Activity::where('name', ActivityType::CREATED_PLATOON)->first();

        $this->assertNotNull($activity);
        $this->assertEquals($platoon->id, $activity->subject_id);
        $this->assertEquals(Platoon::class, $activity->subject_type);
    }

    public function test_activity_not_recorded_without_authenticated_user()
    {
        $division = Division::factory()->create();
        $member = $this->createMember(['division_id' => $division->id]);

        $member->recordActivity(ActivityType::RECRUITED);

        $this->assertDatabaseMissing('activities', [
            'name' => ActivityType::RECRUITED->value,
            'subject_id' => $member->id,
        ]);
    }

    public function test_transfer_activity_includes_destination_properties()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $platoon = Platoon::factory()->create(['division_id' => $division->id, 'name' => 'Delta Platoon']);
        $squad = Squad::factory()->create(['platoon_id' => $platoon->id, 'name' => 'Echo Squad']);
        $member = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer);
        $member->recordActivity(ActivityType::TRANSFERRED, [
            'platoon' => $platoon->name,
            'squad' => $squad->name,
        ]);

        $activity = Activity::where('name', ActivityType::TRANSFERRED)->first();

        $this->assertNotNull($activity);
        $this->assertEquals('Delta Platoon', $activity->properties['platoon']);
        $this->assertEquals('Echo Squad', $activity->properties['squad']);
    }

    public function test_part_time_activity_includes_division_name()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $otherDivision = Division::factory()->create(['name' => 'Test Division']);
        $member = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer);
        $member->recordActivity(ActivityType::ADD_PART_TIME, [
            'division' => $otherDivision->name,
        ]);

        $activity = Activity::where('name', ActivityType::ADD_PART_TIME)->first();

        $this->assertNotNull($activity);
        $this->assertEquals('Test Division', $activity->properties['division']);
    }

    public function test_activity_type_enum_casts_correctly()
    {
        $officer = $this->createOfficer();
        $member = $this->createMember(['division_id' => $officer->member->division_id]);

        $this->actingAs($officer);
        $member->recordActivity(ActivityType::FLAGGED);

        $activity = Activity::where('subject_id', $member->id)->first();

        $this->assertInstanceOf(ActivityType::class, $activity->name);
        $this->assertEquals(ActivityType::FLAGGED, $activity->name);
        $this->assertEquals('Flagged', $activity->name->label());
    }

    public function test_activity_belongs_to_user()
    {
        $officer = $this->createOfficer();
        $member = $this->createMember(['division_id' => $officer->member->division_id]);

        $this->actingAs($officer);
        $member->recordActivity(ActivityType::REMOVED);

        $activity = Activity::where('name', ActivityType::REMOVED)->first();

        $this->assertNotNull($activity->user);
        $this->assertEquals($officer->id, $activity->user->id);
    }

    public function test_activity_morph_to_subject()
    {
        $officer = $this->createOfficer();
        $member = $this->createMember(['division_id' => $officer->member->division_id]);

        $this->actingAs($officer);
        $member->recordActivity(ActivityType::UNASSIGNED);

        $activity = Activity::where('name', ActivityType::UNASSIGNED)->first();

        $this->assertNotNull($activity->subject);
        $this->assertInstanceOf(Member::class, $activity->subject);
        $this->assertEquals($member->id, $activity->subject->id);
    }

    public function test_member_has_activity_relationship()
    {
        $officer = $this->createOfficer();
        $member = $this->createMember(['division_id' => $officer->member->division_id]);

        $this->actingAs($officer);
        $member->recordActivity(ActivityType::RECRUITED);
        $member->recordActivity(ActivityType::ASSIGNED_PLATOON);

        $this->assertCount(2, $member->activity);
        $this->assertTrue($member->activity->pluck('name')->contains(ActivityType::RECRUITED));
        $this->assertTrue($member->activity->pluck('name')->contains(ActivityType::ASSIGNED_PLATOON));
    }

    public function test_empty_properties_stored_as_null()
    {
        $officer = $this->createOfficer();
        $member = $this->createMember(['division_id' => $officer->member->division_id]);

        $this->actingAs($officer);
        $member->recordActivity(ActivityType::RECRUITED);

        $activity = Activity::where('name', ActivityType::RECRUITED)->first();

        $this->assertNull($activity->properties);
    }
}
