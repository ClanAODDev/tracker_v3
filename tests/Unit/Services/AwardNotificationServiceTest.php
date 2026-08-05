<?php

namespace Tests\Unit\Services;

use App\Models\Award;
use App\Models\Division;
use App\Models\MemberAward;
use App\Notifications\Channel\NotifyDivisionMemberAwardsBulk;
use App\Services\AwardNotificationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class AwardNotificationServiceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private AwardNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AwardNotificationService;
        Notification::fake();
    }

    private function enableAwardAlerts(Division $division): void
    {
        $division->settings = array_merge($division->settings, [
            'chat_alerts' => array_merge($division->settings['chat_alerts'], ['member_awarded' => 'officers']),
        ]);
        $division->save();
    }

    #[Test]
    public function sends_one_notification_per_division_even_with_multiple_award_types()
    {
        $division = $this->createActiveDivision();
        $this->enableAwardAlerts($division);

        $fiveYear = Award::factory()->global()->create(['name' => '5 Year Tenure']);
        $tenYear  = Award::factory()->global()->create(['name' => '10 Year Tenure']);

        $memberA = $this->createMember(['division_id' => $division->id]);
        $memberB = $this->createMember(['division_id' => $division->id]);
        $memberC = $this->createMember(['division_id' => $division->id]);

        $awards = Collection::make([
            MemberAward::factory()->approved()->create(['member_id' => $memberA->id, 'award_id' => $fiveYear->id]),
            MemberAward::factory()->approved()->create(['member_id' => $memberB->id, 'award_id' => $fiveYear->id]),
            MemberAward::factory()->approved()->create(['member_id' => $memberC->id, 'award_id' => $tenYear->id]),
        ]);

        $this->service->notifyBulkApproval($awards);

        Notification::assertSentToTimes($division, NotifyDivisionMemberAwardsBulk::class, 1);
    }

    #[Test]
    public function skips_divisions_with_award_alerts_disabled()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $award    = Award::factory()->global()->create();

        $awards = Collection::make([
            MemberAward::factory()->approved()->create(['member_id' => $member->id, 'award_id' => $award->id]),
        ]);

        $this->service->notifyBulkApproval($awards);

        Notification::assertNothingSent();
    }

    #[Test]
    public function sends_a_separate_notification_per_division()
    {
        $divisionA = $this->createActiveDivision();
        $divisionB = $this->createActiveDivision();
        $this->enableAwardAlerts($divisionA);
        $this->enableAwardAlerts($divisionB);

        $award = Award::factory()->global()->create();

        $memberA = $this->createMember(['division_id' => $divisionA->id]);
        $memberB = $this->createMember(['division_id' => $divisionB->id]);

        $awards = Collection::make([
            MemberAward::factory()->approved()->create(['member_id' => $memberA->id, 'award_id' => $award->id]),
            MemberAward::factory()->approved()->create(['member_id' => $memberB->id, 'award_id' => $award->id]),
        ]);

        $this->service->notifyBulkApproval($awards);

        Notification::assertSentToTimes($divisionA, NotifyDivisionMemberAwardsBulk::class, 1);
        Notification::assertSentToTimes($divisionB, NotifyDivisionMemberAwardsBulk::class, 1);
    }

    #[Test]
    public function members_without_a_division_are_skipped()
    {
        $member = $this->createMember(['division_id' => 0]);
        $award  = Award::factory()->global()->create();

        $awards = Collection::make([
            MemberAward::factory()->approved()->create(['member_id' => $member->id, 'award_id' => $award->id]),
        ]);

        $this->service->notifyBulkApproval($awards);

        Notification::assertNothingSent();
    }
}
