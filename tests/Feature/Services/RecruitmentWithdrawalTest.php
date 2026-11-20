<?php

namespace Tests\Feature\Services;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\MemberRequest;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RecruitmentWithdrawalTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function deleting_an_unapproved_request_withdraws_a_new_discord_recruit()
    {
        $division  = $this->createActiveDivision();
        $recruiter = $this->createMemberWithUser(['division_id' => $division->id]);
        $recruit   = $this->createMember(['division_id' => $division->id]);
        $applicant = User::factory()->create(['discord_id' => '555000111', 'member_id' => $recruit->id]);
        $request   = MemberRequest::factory()->create(['member_id' => $recruit->id, 'division_id' => $division->id]);

        $this->actingAs($recruiter);
        $request->delete();

        $this->assertSame(0, $recruit->fresh()->division_id);
        $this->assertNull($applicant->fresh()->member_id);
        $this->assertTrue(User::pendingDiscord()->whereKey($applicant->id)->exists());
        $this->assertDatabaseHas(Note::class, ['member_id' => $recruit->id, 'author_id' => $recruiter->id]);
        $this->assertDatabaseHas(Activity::class, ['subject_id' => $recruit->id, 'name' => ActivityType::REMOVED->value]);
    }

    #[Test]
    public function a_returning_member_keeps_their_account_when_the_request_is_deleted()
    {
        $division  = $this->createActiveDivision();
        $recruiter = $this->createMemberWithUser(['division_id' => $division->id]);
        $returning = $this->createMember(['division_id' => $division->id, 'created_at' => now()->subYears(2)]);
        $account   = User::factory()->create(['discord_id' => '555000222', 'member_id' => $returning->id]);
        $request   = MemberRequest::factory()->create(['member_id' => $returning->id, 'division_id' => $division->id]);

        $this->actingAs($recruiter);
        $request->delete();

        $this->assertSame(0, $returning->fresh()->division_id);
        $this->assertSame($returning->id, $account->fresh()->member_id);
    }

    #[Test]
    public function deleting_an_approved_request_changes_nothing()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $request  = MemberRequest::factory()->create([
            'member_id'   => $member->id,
            'division_id' => $division->id,
            'approved_at' => now(),
        ]);

        $request->delete();

        $this->assertSame($division->id, $member->fresh()->division_id);
    }

    #[Test]
    public function a_member_who_has_since_moved_division_is_left_alone()
    {
        $original = $this->createActiveDivision();
        $current  = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $current->id]);
        $request  = MemberRequest::factory()->create(['member_id' => $member->id, 'division_id' => $original->id]);

        $request->delete();

        $this->assertSame($current->id, $member->fresh()->division_id);
    }
}
